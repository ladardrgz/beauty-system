<?php
require_once "models/UsuarioModel.php";
require_once "core/Sesion.php";

class MiPerfilController
{
    private $usuarioModel;

    // ===================================================
    // CONSTRUCTOR
    // ===================================================
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /* ===================================================
       SECCIÓN: PERFIL DE USUARIO
       =================================================== */
    public function verFrmPerfil()
    {
        // 🔹 Verificar sesión activa
        Sesion::requerirLogin();

        // 🔹 Obtener usuario actual desde la sesión
        $usuarioSesion = Sesion::obtenerUsuario();
        $id_usuario = $usuarioSesion["id_usuario"];

        // 🔹 Obtener información completa del usuario
        $datosPerfil = $this->usuarioModel->obtenerInformacionCompleta($id_usuario);

        if (!$datosPerfil) {
            $mensajeError = "No se pudieron obtener los datos del perfil.";
            $vista = 'views/errores/error_general.php';
            require_once 'views/layouts/main.php';
            return;
        }

        // 🔹 Definir vista principal del perfil
        $vista = 'views/perfil/mi_perfil.php';
        require_once 'views/layouts/main.php';
    }

        /* ===================================================
       SECCIÓN: ACTUALIZACIÓN DE PERFIL (desde modal)
       =================================================== */
    public function actualizarPerfil()
    {
        Sesion::requerirLogin();

        require_once "models/PersonaModel.php";
        $personaModel = new PersonaModel();

        $usuarioSesion = Sesion::obtenerUsuario();
        $idPersona = $usuarioSesion['relacion_persona'] ?? null;

        if (!$idPersona) {
            echo json_encode(["success" => false, "mensaje" => "No se encontró la persona asociada al usuario."]);
            return;
        }

        // =============================
        // Captura de datos enviados
        // =============================
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $fecha    = $_POST['fecha_nac'] ?? null;
        $genero   = $_POST['genero'] ?? null;

        $calle    = trim($_POST['calle_direccion'] ?? '');
        $numero   = trim($_POST['numero_direccion'] ?? '');
        $piso     = trim($_POST['piso_direccion'] ?? '');
        $info     = trim($_POST['info_extra_direccion'] ?? '');
        $idBarrio = $_POST['barrio'] ?? null;

        // =============================
        // Validaciones básicas
        // =============================
        if (empty($nombre) || empty($apellido) || empty($calle) || empty($numero) || empty($idBarrio)) {
            echo json_encode(["success" => false, "mensaje" => "Por favor complete todos los campos obligatorios."]);
            return;
        }

        // =============================
        // Actualización de datos
        // =============================
        $okDatos = $personaModel->actualizarDatos($idPersona, $nombre, $apellido, $fecha, $genero);

        // Obtener id_domicilio actual
        $personaActual = $personaModel->obtenerPorId($idPersona);
        $idDomicilio = $personaActual["id_domicilio"] ?? null;

        $okDomicilio = false;
        if ($idDomicilio) {
            $okDomicilio = $personaModel->actualizarDomicilio($idDomicilio, $calle, $numero, $piso, $info, $idBarrio);
        }

        // =============================
        // Respuesta final en JSON
        // =============================
        if ($okDatos && $okDomicilio) {
            echo json_encode([
                "success" => true,
                "mensaje" => "✅ Perfil actualizado correctamente."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "❌ No se pudo actualizar completamente el perfil."
            ]);
        }
    }

    /* ===================================================
       SECCIÓN: SEGURIDAD DE LA CUENTA
       =================================================== */
    public function verFrmSeguridad()
    {
        // 🔹 Requiere que el usuario esté logueado
        Sesion::requerirLogin();

        // 🔹 Definimos la vista de seguridad de la cuenta
        $vista = 'views/perfil/seguridad_cuenta.php';
        require_once 'views/layouts/main.php';
    }

  /* ===================================================
   ACTUALIZAR CONTRASEÑA (Seguridad de la cuenta)
   =================================================== */
public function actualizarContrasena()
{
    Sesion::requerirLogin();
    header("Content-Type: application/json; charset=utf-8");

    try {
        $usuarioSesion = Sesion::obtenerUsuario();
        $idUsuario = $usuarioSesion["id_usuario"] ?? null;

        if (!$idUsuario) {
            echo json_encode(["success" => false, "mensaje" => "No se encontró el usuario activo."]);
            return;
        }

        $actual    = trim($_POST["actual"] ?? "");
        $nueva     = trim($_POST["nueva"] ?? "");
        $confirmar = trim($_POST["confirmar"] ?? "");

        // =============================
        // VALIDACIONES BÁSICAS
        // =============================
        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            echo json_encode(["success" => false, "mensaje" => "Debe completar todos los campos."]);
            return;
        }

        if ($nueva !== $confirmar) {
            echo json_encode(["success" => false, "mensaje" => "Las contraseñas nuevas no coinciden."]);
            return;
        }

        // =============================
        // OBTENER CONTRASEÑA ACTUAL
        // =============================
        $usuario = $this->usuarioModel->obtenerPorId($idUsuario);

        if (!$usuario || empty($usuario["password_usuario"])) {
            echo json_encode(["success" => false, "mensaje" => "No se encontró información de contraseña para este usuario."]);
            return;
        }

        $hashAlmacenado = $usuario["password_usuario"];

        // =============================
        // VERIFICAR CONTRASEÑA ACTUAL
        // =============================
        if (!password_verify($actual, $hashAlmacenado)) {
            echo json_encode(["success" => false, "mensaje" => "La contraseña actual no es correcta."]);
            return;
        }

        // =============================
        // VALIDAR QUE LA NUEVA NO SEA IGUAL
        // =============================
        if (password_verify($nueva, $hashAlmacenado)) {
            echo json_encode(["success" => false, "mensaje" => "La nueva contraseña no puede ser igual a la actual."]);
            return;
        }

        // =============================
        // ENCRIPTAR Y ACTUALIZAR
        // =============================
        $nuevaHash = password_hash($nueva, PASSWORD_DEFAULT);
        $resultado = $this->usuarioModel->actualizarContrasena($idUsuario, $nuevaHash);

        if (
            (is_array($resultado) && ($resultado["success"] ?? false)) ||
            $resultado === true
        ) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Tu contraseña fue actualizada correctamente 🔐"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "No se pudo actualizar la contraseña. Intente nuevamente."
            ]);
        }
    } catch (Throwable $e) {
        echo json_encode([
            "success" => false,
            "mensaje" => "Error interno: " . $e->getMessage()
        ]);
    }
}


}
