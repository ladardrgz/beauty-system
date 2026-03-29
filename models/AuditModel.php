<?php
require_once 'Conexion.php';

class AuditModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->Conectar();
    }

    /**
     * Registrar evento de auditoría general
     */
public function registrarEvento($tabla, $accion, $idRegistro = null, $datosAntes = null, $datosDespues = null)
{
    // Obtener usuario desde tu sesión real
    $usuarioId = ($_SESSION['usuario']['id_usuario']) ?? null;

    $sql = "INSERT INTO auditoria 
            (tabla_afectada, accion, id_registro, datos_antes, datos_despues, usuario_id, ip_origen, user_agent) 
            VALUES (:tabla, :accion, :idRegistro, :datosAntes, :datosDespues, :usuario_id, :ip, :userAgent)";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':tabla'       => $tabla,
        ':accion'      => $accion,
        ':idRegistro'  => $idRegistro,
        ':datosAntes'  => $datosAntes ? json_encode($datosAntes, JSON_UNESCAPED_UNICODE) : null,
        ':datosDespues'=> $datosDespues ? json_encode($datosDespues, JSON_UNESCAPED_UNICODE) : null,
        ':usuario_id'  => $usuarioId,
        ':ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        ':userAgent'   => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}


    /**
     * Listado de auditoría con filtros y paginación
     */
    public function listar($pagina = 1, $registros = 10, $filtros = [])
    {
        $offset = ($pagina - 1) * $registros;
        $where = [];
        $params = [];

        if (!empty($filtros['usuario'])) {
            $where[] = "u.nombre_usuario LIKE :usuario";
            $params[':usuario'] = "%{$filtros['usuario']}%";
        }

        if (!empty($filtros['accion'])) {
            $where[] = "a.accion = :accion";
            $params[':accion'] = $filtros['accion'];
        }

        if (!empty($filtros['tabla'])) {
            $where[] = "a.tabla_afectada = :tabla";
            $params[':tabla'] = $filtros['tabla'];
        }

        if (!empty($filtros['desde'])) {
            $where[] = "a.fecha_evento >= :desde";
            $params[':desde'] = $filtros['desde'] . " 00:00:00";
        }

        if (!empty($filtros['hasta'])) {
            $where[] = "a.fecha_evento <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . " 23:59:59";
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT a.id_auditoria, a.tabla_afectada, a.accion, a.id_registro,
                       a.fecha_evento, a.ip_origen,
                       u.nombre_usuario AS usuario
                FROM auditoria a
                LEFT JOIN usuario u ON u.id_usuario = a.usuario_id
                $sqlWhere
                ORDER BY a.id_auditoria DESC
                LIMIT :offset, :registros";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':registros', (int)$registros, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar total de registros para paginación
     */
    public function contar($filtros = [])
    {
        $where = [];
        $params = [];

        if (!empty($filtros['usuario'])) {
            $where[] = "u.nombre_usuario LIKE :usuario";
            $params[':usuario'] = "%{$filtros['usuario']}%";
        }
        if (!empty($filtros['accion'])) {
            $where[] = "a.accion = :accion";
            $params[':accion'] = $filtros['accion'];
        }
        if (!empty($filtros['tabla'])) {
            $where[] = "a.tabla_afectada = :tabla";
            $params[':tabla'] = $filtros['tabla'];
        }
        if (!empty($filtros['desde'])) {
            $where[] = "a.fecha_evento >= :desde";
            $params[':desde'] = $filtros['desde'] . " 00:00:00";
        }
        if (!empty($filtros['hasta'])) {
            $where[] = "a.fecha_evento <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . " 23:59:59";
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) AS total
                FROM auditoria a
                LEFT JOIN usuario u ON u.id_usuario = a.usuario_id
                $sqlWhere";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Obtener detalle completo por ID
     */
    public function obtenerDetalle($id)
    {
        $sql = "SELECT a.*, u.nombre_usuario AS usuario
                FROM auditoria a
                LEFT JOIN usuario u ON u.id_usuario = a.usuario_id
                WHERE a.id_auditoria = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function obtenerEstadisticas()
{
    $sql = "SELECT accion, COUNT(*) as cantidad
            FROM auditoria
            WHERE accion IN ('INSERT', 'UPDATE', 'DELETE')
            GROUP BY accion";

    $stmt = $this->db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalizo para asegurarnos que siempre estén los 3 tipos
    $estadisticas = [
        'INSERT' => 0,
        'UPDATE' => 0,
        'DELETE' => 0
    ];

    foreach ($result as $row) {
        $estadisticas[$row['accion']] = (int)$row['cantidad'];
    }

    return $estadisticas;
}

}
