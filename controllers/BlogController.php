<?php
require_once 'models/BlogModel.php';

class BlogController {

    private $model;

    public function __construct() {
        $this->model = new BlogModel();
    }

    public function verSeccionBlog()
    {
        Sesion::iniciar();
        $articulos = $this->model->listar();
        $vista = 'views/blog/admin/listar.php';
        require_once 'views/layouts/main.php';
    }

    public function crear()
    {
        Sesion::iniciar();
        $vista = 'views/blog/admin/crear.php';
        require_once 'views/layouts/main.php';
    }

    public function guardar()
    {
        Sesion::iniciar();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Sesion::obtenerUsuario();

            $data = [
                'titulo'     => $_POST['titulo'] ?? '',
                'contenido'  => $_POST['contenido'] ?? '',
                'id_usuario' => $usuario['id_usuario'] ?? null
            ];

            if (!$data['id_usuario']) {
                $_SESSION['mensaje'] = [
                    'tipo' => 'error',
                    'texto' => 'No se detectó usuario logueado.'
                ];
                header("Location: index.php?controller=Blog&action=verSeccionBlog");
                exit;
            }

            if ($this->model->guardar($data)) {
                $_SESSION['mensaje'] = [
                    'tipo' => 'success',
                    'texto' => 'Publicación creada correctamente.'
                ];
            } else {
                $_SESSION['mensaje'] = [
                    'tipo' => 'error',
                    'texto' => 'Error al guardar la publicación.'
                ];
            }

            header("Location: index.php?controller=Blog&action=verSeccionBlog");
            exit;
        }
    }

    public function editar()
    {
        Sesion::iniciar();
        $id = $_GET['id'] ?? null;
        $post = $this->model->obtener($id);
        $vista = 'views/blog/admin/editar.php';
        require_once 'views/layouts/main.php';
    }

    public function actualizar()
    {
        Sesion::iniciar();

        if ($this->model->actualizar([
            'id_post'   => $_POST['id_post'],
            'titulo'    => $_POST['titulo'],
            'contenido' => $_POST['contenido']
        ])) {
            $_SESSION['mensaje'] = [
                'tipo' => 'success',
                'texto' => 'Publicación actualizada correctamente.'
            ];
        } else {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'texto' => 'Error al actualizar la publicación.'
            ];
        }

        header("Location: index.php?controller=Blog&action=verSeccionBlog");
        exit;
    }

    public function eliminar()
    {
        Sesion::iniciar();
        $id = $_GET['id'] ?? null;

        if ($this->model->eliminar($id)) {
            $_SESSION['mensaje'] = [
                'tipo' => 'success',
                'texto' => 'Publicación eliminada correctamente.'
            ];
        } else {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'texto' => 'Error al eliminar la publicación.'
            ];
        }

        header("Location: index.php?controller=Blog&action=verSeccionBlog");
        exit;
    }

    public function verComentarios()
    {
        Sesion::iniciar();
        $id = $_GET['id'] ?? null;
        $post = $this->model->obtener($id);
        $comentarios = $this->model->obtenerComentarios($id);
        $vista = 'views/blog/admin/comentarios.php';
        require_once 'views/layouts/main.php';
    }
      /* ==========================================================
     * 🔹 ÁREA PÚBLICA (SIN DUPLICAR CONTROLADOR NI MODEL)
     * ==========================================================*/

    // Muestra todos los posts públicamente
    public function publico()
    {
        $articulos = $this->model->listar();
        $vista = 'views/blog/publico/listar.php';
        require_once 'views/layouts/main.php';
    }

    // Ver un post con comentarios y poder comentar
    public function verPublico()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?controller=Blog&action=publico");
            exit;
        }

        $post = $this->model->obtener($id);
        $comentarios = $this->model->obtenerComentarios($id);
        $vista = 'views/blog/publico/ver.php';
        require_once 'views/layouts/main.php';
    }

    // Guardar comentario público
    public function guardarComentarioPublico()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_post'    => $_POST['id_post'],
                'id_cliente' => $_POST['id_cliente'] ?? 1, 
                'comentario' => $_POST['comentario'] ?? ''
            ];

            if ($this->model->guardarComentario($data)) {
                $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Comentario enviado correctamente.'];
            } else {
                $_SESSION['mensaje'] = ['tipo' => 'error', 'texto' => 'Error al enviar el comentario.'];
            }

            header("Location: index.php?controller=Blog&action=verPublico&id={$data['id_post']}");
            exit;
        }
    }
}
