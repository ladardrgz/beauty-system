<?php
require_once 'Conexion.php';

class ModuloModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->Conectar();
    }

    /**
     * Obtiene todos los módulos activos.
     *
     * @return array
     */
    public function obtenerTodos(): array
    {
        $sql = "SELECT id_modulo, descripcion_modulo
                FROM modulo
                WHERE activo_modulo = 1
                ORDER BY descripcion_modulo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si ya existe un módulo con ese nombre (case-insensitive).
     *
     * @param string $nombre
     * @return bool
     */
    public function existeModulo(string $nombre): bool
    {
        $sql = "SELECT COUNT(*)
                FROM modulo
                WHERE LOWER(descripcion_modulo) = LOWER(:nombre)
                  AND activo_modulo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo módulo.
     *
     * @param string $nombre
     * @return bool
     */
    public function crear(string $nombre): bool
    {
        $sql = "INSERT INTO modulo (descripcion_modulo)
                VALUES (:nombre)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':nombre' => $nombre]);
    }

    /**
     * Desactiva (elimina lógicamente) un módulo.
     *
     * @param int $id
     * @return bool
     */
    public function desactivar(int $id): bool
    {
        $sql = "UPDATE modulo
                SET activo_modulo = 0
                WHERE id_modulo = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtiene los módulos autorizados para un perfil específico.
     * Devuelve un array plano con las descripciones de los módulos.
     *
     * @param int $idPerfil
     * @return array
     */
    public function obtenerModulosPorPerfil(int $idPerfil): array
    {
        $sql = "SELECT m.descripcion_modulo
                FROM modulo m
                INNER JOIN modulo_perfil mp ON m.id_modulo = mp.relacion_modulo
                WHERE mp.relacion_perfil = :perfil
                AND m.activo_modulo = 1
                ORDER BY m.descripcion_modulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil' => $idPerfil]);

        // 🔹 Devuelve directamente un array plano como ['Catálogo', 'Usuarios', 'Clientes']
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
