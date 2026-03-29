<?php
// ============================================================
// Archivo: core/Sesion.php
// ------------------------------------------------------------
// Clase encargada de gestionar la sesión de usuario:
//  - Inicia, obtiene y destruye sesiones PHP.
//  - Registra la sesión activa/inactiva en la base de datos.
//  - Mantiene siempre un perfil activo, incluso sin autenticación.
//  - Ahora también gestiona el carrito de compras de forma segura.
// ============================================================

class Sesion
{
    /**
     * Inicia la sesión PHP si aún no está activa.
     */
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Registra al usuario autenticado dentro de la sesión
     * y marca su sesión como activa en la base de datos.
     */
    public static function establecerUsuario(array $usuario)
    {
        self::iniciar();

        // Aseguramos claves esperadas para consistencia
        $_SESSION['usuario'] = [
            'id_usuario'         => $usuario['id_usuario']         ?? null,
            'nombre_usuario'     => $usuario['nombre_usuario']     ?? 'Desconocido',
            'relacion_persona'   => $usuario['relacion_persona']   ?? null,
            'relacion_perfil'    => $usuario['relacion_perfil']    ?? 5, // invitado por defecto
            'descripcion_perfil' => $usuario['descripcion_perfil'] ?? 'Invitado'
        ];

        // Activar sesión en BD (solo si tiene id_usuario)
        if (!empty($usuario['id_usuario'])) {
            require_once 'models/SesionModel.php';
            $modelo = new SesionModel();
            $modelo->marcarSesionActiva($usuario['id_usuario']);
        }
    }

    /**
     * Devuelve los datos completos del usuario en sesión.
     */
    public static function obtenerUsuario()
    {
        self::iniciar();
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Verifica si hay un usuario autenticado.
     */
    public static function usuarioAutenticado(): bool
    {
        self::iniciar();
        return isset($_SESSION['usuario']['id_usuario']) &&
               $_SESSION['usuario']['id_usuario'] !== null;
    }

    /**
     * Destruye la sesión actual del usuario.
     */
    public static function destruir()
    {
        self::iniciar();

        // Marcar sesión como inactiva en BD (si corresponde)
        if (!empty($_SESSION['usuario']['id_usuario'])) {
            require_once 'models/SesionModel.php';
            $modelo = new SesionModel();
            $modelo->marcarSesionInactiva($_SESSION['usuario']['id_usuario']);
        }

        // Limpiar la sesión PHP
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Reiniciar como invitado
        self::inicializarInvitado();
    }

    /**
     * Redirige al login si el usuario no está autenticado.
     */
    public static function requerirLogin()
    {
        if (!self::usuarioAutenticado()) {
            header("Location: index.php?controller=Login&action=login");
            exit;
        }
    }

    /**
     * Inicializa sesión como "Invitado".
     */
    public static function inicializarInvitado()
    {
        self::iniciar();

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = [
                'id_usuario'         => null,
                'nombre_usuario'     => 'Invitado',
                'relacion_persona'   => null,
                'relacion_perfil'    => 5,
                'descripcion_perfil' => 'Invitado'
            ];
        }
    }

    /**
     * Devuelve el código del perfil actual.
     */
    public static function obtenerPerfil(): int
    {
        self::iniciar();
        return $_SESSION['usuario']['relacion_perfil'] ?? 5;
    }

    /**
     * Devuelve el nombre descriptivo del perfil.
     */
    public static function obtenerNombrePerfil(): string
    {
        self::iniciar();
        return $_SESSION['usuario']['descripcion_perfil'] ?? 'Invitado';
    }

    // ============================================================
    // BLOQUE ADICIONAL: ASEGURAR PERFIL INVITADO
    // ============================================================

    /**
     * Garantiza que siempre haya un perfil (incluso sin login)
     * Ideal para sesiones de carrito o navegación como invitado.
     */
    public static function asegurarInvitado()
    {
        self::iniciar();

        if (empty($_SESSION['usuario'])) {
            $_SESSION['usuario'] = [
                'id_usuario'         => null,
                'nombre_usuario'     => 'Invitado',
                'relacion_persona'   => null,
                'relacion_perfil'    => 5,
                'descripcion_perfil' => 'Invitado'
            ];
        }
    }

// ============================================================
// MÉTODOS GENÉRICOS DE MANEJO DE SESIÓN (Versión limpia)
// ============================================================

/**
 * Guarda un valor en sesión bajo una clave específica.
 */
public function set(string $clave, $valor): void
{
    $_SESSION[$clave] = $valor;
}

/**
 * Obtiene el valor almacenado en sesión para una clave dada.
 * Retorna null si no existe.
 */
public function get(string $clave)
{
    return $_SESSION[$clave] ?? null;
}

/**
 * Verifica si una clave existe en la sesión.
 */
public function has(string $clave): bool
{
    return isset($_SESSION[$clave]);
}

/**
 * Elimina una clave específica de la sesión.
 */
public function remove(string $clave): void
{
    if (isset($_SESSION[$clave])) {
        unset($_SESSION[$clave]);
    }
}

/**
 * Elimina todos los datos de sesión y destruye la sesión.
 */
public function destroy(): void
{
    session_unset();
    session_destroy();
}

/**
 * Retorna todo el contenido actual de la sesión.
 */
public function all(): array
{
    return $_SESSION;
}

/**
 * Verifica si el usuario autenticado es cliente y guarda id_cliente en sesión.
 * Se debe llamar después del login o antes de usar módulos de cliente.
 */
public static function establecerClienteDesdeBD()
{
    self::iniciar();

    // Si ya está en sesión, no lo buscamos de nuevo
    if (!empty($_SESSION['id_cliente'])) {
        return;
    }

    // Validar que hay usuario autenticado
    if (empty($_SESSION['usuario']['relacion_persona'])) {
        return;
    }

    $idPersona = $_SESSION['usuario']['relacion_persona'];

    // Buscar si existe un cliente relacionado
    require_once 'models/HistorialModel.php';
    $modelo = new HistorialModel();
    $idCliente = $modelo->obtenerIdClientePorRelacion($idPersona);

    if ($idCliente) {
        $_SESSION['id_cliente'] = $idCliente;
    }
}


}