<?php
require_once 'models/Conexion.php';

class BlogModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->Conectar();
    }

    public function listar()
    {
        $sql = "SELECT p.id_post, p.titulo, p.contenido, p.fecha_publicacion,
                       u.nombre_usuario AS autor
                FROM blog_post p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                ORDER BY p.fecha_publicacion DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar(array $data)
    {
        $sql = "INSERT INTO blog_post (id_usuario, titulo, contenido)
                VALUES (:id_usuario, :titulo, :contenido)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':titulo'     => $data['titulo'],
            ':contenido'  => $data['contenido']
        ]);
    }

    public function obtener($id_post)
    {
        $sql = "SELECT p.id_post, p.titulo, p.contenido, p.fecha_publicacion,
                       u.nombre_usuario AS autor
                FROM blog_post p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE p.id_post = :id_post";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_post' => $id_post]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar(array $data)
    {
        $sql = "UPDATE blog_post
                SET titulo = :titulo,
                    contenido = :contenido
                WHERE id_post = :id_post";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_post'   => $data['id_post'],
            ':titulo'    => $data['titulo'],
            ':contenido' => $data['contenido']
        ]);
    }

    public function eliminar($id_post)
    {
        $sql = "DELETE FROM blog_post WHERE id_post = :id_post";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id_post' => $id_post]);
    }

    public function obtenerComentarios($id_post)
    {
        $sql = "SELECT c.id_comentario,
                       c.comentario,
                       c.fecha,
                       CONCAT(p.nombre_persona, ' ', p.apellido_persona) AS cliente
                FROM comentarios_blog c
                JOIN cliente cl ON c.id_cliente = cl.id_cliente
                JOIN persona p ON cl.relacion_persona = p.id_persona
                WHERE c.id_post = :id_post
                ORDER BY c.fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_post' => $id_post]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarComentarios($id_post)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM comentarios_blog
                WHERE id_post = :id_post";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_post' => $id_post]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function guardarComentario(array $data)
    {
        $sql = "INSERT INTO comentarios_blog (id_post, id_cliente, comentario)
                VALUES (:id_post, :id_cliente, :comentario)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_post'    => $data['id_post'],
            ':id_cliente' => $data['id_cliente'],
            ':comentario' => $data['comentario']
        ]);
    }
}
