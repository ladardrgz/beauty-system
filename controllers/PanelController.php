<?php
require_once 'core/Sesion.php';
require_once 'core/ModuloHelper.php';
require_once 'core/DashboardHelper.php';

class PanelController
{
    public function dashboard()
    {
        // ============================
        // Seguridad y sesión
        // ============================
        Sesion::iniciar();
        Sesion::requerirLogin();

        $usuario = Sesion::obtenerUsuario();

        if (!$usuario) {
            echo "<div class='alert alert-danger text-center mt-4'>
                ⚠ No hay datos del usuario en sesión.
            </div>";
            exit;
        }

        // ============================
        // Datos principales del usuario
        // ============================
        $usuarioNombre     = $usuario['nombre_usuario'] ?? 'Invitado';
        $perfilDescripcion = $usuario['descripcion_perfil'] ?? null;
        $perfilId          = $usuario['relacion_perfil'] ?? null;
        $idUsuario         = $usuario['id_usuario'] ?? null;

        // ============================
        // Validaciones críticas
        // ============================
        if (!$perfilDescripcion || !$perfilId) {
            echo "<div class='alert alert-danger text-center mt-4'>
                ❌ Perfil del usuario no definido o inválido.
            </div>";
            exit;
        }

        if (!$idUsuario) {
            echo "<div class='alert alert-danger text-center mt-4'>
                ⚠ No se pudo detectar el ID del usuario en sesión.
            </div>";
            exit;
        }

        // ============================
        // Cargar módulos visibles
        // ============================
        $modulos = ModuloHelper::obtenerModulosAutorizados($perfilId);

        // ============================
        // Determinar dashboard por perfil
        // ============================
        $contenido = DashboardHelper::obtenerDashboardPorPerfil($perfilDescripcion);

        if (!$contenido || !file_exists($contenido)) {
            echo "<div class='alert alert-warning text-center mt-4'>
                ⚠ El dashboard para el perfil <b>{$perfilDescripcion}</b> no está disponible.
            </div>";
            exit;
        }

        // ============================
        // Datos base para la vista 
        // ============================
        $titulo = "Panel de inicio | MizzaStore";
        $vista  = $contenido;
        $data   = [];

        // ============================================================
        // 📦 DETALLE EXCLUSIVO PARA CLIENTE: Cargar pedidos y envíos
        // ============================================================
        if ($perfilDescripcion === 'Cliente') {
            require_once 'models/Conexion.php';
            require_once 'models/PedidoModel.php';

            $conexion = new Conexion();
            $db = $conexion->Conectar();
            $pedidoModel = new PedidoModel($db);

            // Obtener id_cliente REAL desde la base, usando id_usuario de sesión
            $idCliente = $pedidoModel->obtenerClienteIdPorUsuario($idUsuario);

            if (!$idCliente) {
                $data['errorPedidos'] = "⚠ No se encontró un cliente asociado al usuario actual.";
            } else {
                $data['pedidos'] = $pedidoModel->obtenerPedidosPorCliente($idCliente);
                $data['pedidos_envio'] = $pedidoModel->obtenerPedidosConEstadoEnvio($idCliente);

                if (empty($data['pedidos'])) {
                    $data['errorPedidos'] = "No tienes pedidos registrados aún.";
                }
            }
        }

        // ============================
        // Renderizar vista final
        // ============================
        require_once 'views/layouts/main.php';
    }
}
