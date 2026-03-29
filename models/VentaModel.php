<?php
require_once 'Conexion.php';

class VentaModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->Conectar();
    }

    // === Listar pedidos con filtros y paginación ===
    public function obtenerPedidosPaginado($documento = '', $contacto = '', $pedido = '', $limite = 10, $offset = 0)
    {
        $sql = "SELECT 
                    p.id_pedido,
                    p.fecha_pedido,
                    p.monto_total,
                    per.nombre_persona,
                    per.apellido_persona,
                    dd.descripcion_documento AS documento,
                    dc.descripcion_contacto AS contacto
                FROM pedido p
                INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                INNER JOIN persona per ON c.relacion_persona = per.id_persona
                LEFT JOIN detalle_documento dd ON per.id_detalle_documento = dd.id_detalle_documento
                LEFT JOIN detalle_contacto dc  ON per.id_detalle_contacto = dc.id_detalle_contacto
                WHERE 1=1";

        $params = [];

        if ($pedido) {
            $sql .= " AND p.id_pedido = :pedido";
            $params[':pedido'] = $pedido;
        }
        if ($documento) {
            $sql .= " AND dd.descripcion_documento LIKE :documento";
            $params[':documento'] = "%$documento%";
        }
        if ($contacto) {
            $sql .= " AND dc.descripcion_contacto LIKE :contacto";
            $params[':contacto'] = "%$contacto%";
        }

        $sql .= " ORDER BY p.fecha_pedido DESC LIMIT :offset, :limite";
        $stmt = $this->db->prepare($sql);

        // Bind dinámicos
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }

        // Bind de paginación (clave)
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === Contar total de registros (sin LIMIT) ===
    public function contarPedidos($documento = '', $contacto = '', $pedido = '')
    {
        $sql = "SELECT COUNT(*) AS total
                FROM pedido p
                INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                INNER JOIN persona per ON c.relacion_persona = per.id_persona
                LEFT JOIN detalle_documento dd ON per.id_detalle_documento = dd.id_detalle_documento
                LEFT JOIN detalle_contacto dc  ON per.id_detalle_contacto = dc.id_detalle_contacto
                WHERE 1=1";

        $params = [];

        if ($pedido) {
            $sql .= " AND p.id_pedido = :pedido";
            $params[':pedido'] = $pedido;
        }
        if ($documento) {
            $sql .= " AND dd.descripcion_documento LIKE :documento";
            $params[':documento'] = "%$documento%";
        }
        if ($contacto) {
            $sql .= " AND dc.descripcion_contacto LIKE :contacto";
            $params[':contacto'] = "%$contacto%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // === Detalle de pedido ===
    public function obtenerDetallePedido($idPedido)
    {
        // Cabecera
        $sqlPedido = "SELECT 
                        p.id_pedido,
                        p.fecha_pedido,
                        p.monto_total,
                        per.nombre_persona,
                        per.apellido_persona,
                        dd.descripcion_documento AS documento,
                        dc.descripcion_contacto AS contacto
                    FROM pedido p
                    INNER JOIN cliente c   ON p.id_cliente = c.id_cliente
                    INNER JOIN persona per ON c.relacion_persona = per.id_persona
                    LEFT JOIN detalle_documento dd ON per.id_detalle_documento = dd.id_detalle_documento
                    LEFT JOIN detalle_contacto dc  ON per.id_detalle_contacto = dc.id_detalle_contacto
                    WHERE p.id_pedido = :idPedido";

        $stmt = $this->db->prepare($sqlPedido);
        $stmt->execute([':idPedido' => $idPedido]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return null;
        }

        // Productos
        $sqlProductos = "SELECT 
                            dp.cantidad_producto,
                            dp.precio_unitario,
                            pr.nombre_producto
                        FROM detalle_pedido dp
                        INNER JOIN producto pr ON dp.id_producto = pr.id_producto
                        WHERE dp.id_pedido = :idPedido";

        $stmt2 = $this->db->prepare($sqlProductos);
        $stmt2->execute([':idPedido' => $idPedido]);
        $productos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return [
            'pedido' => $pedido,
            'productos' => $productos
        ];
    }
}
