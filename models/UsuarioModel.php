<?php
require_once "Conexion.php";
require_once "MasterModel.php";

class Usuario {
    private $conn;
    private $masterModel;

    private $id_usuario;
    private $nombre_usuario;
    private $password_usuario;
    private $cuenta_activada;
    private $relacion_persona;
    private $relacion_perfil;
    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->Conectar();
        $this->masterModel = new MasterModel($this->conn);
    }
    public function getId() { return $this->id_usuario; }
    public function getNombreUsuario() { return $this->nombre_usuario; }
    public function getCuentaActivada() { return $this->cuenta_activada; }
    public function getRelacionPersona() { return $this->relacion_persona; }
    public function getRelacionPerfil() { return $this->relacion_perfil; }

    public function setNombreUsuario($nombre) { $this->nombre_usuario = $nombre; }
    public function setCuentaActivada($estado) { $this->cuenta_activada = $estado; }
    public function setRelacionPersona($id) { $this->relacion_persona = $id; }
    public function setRelacionPerfil($id) { $this->relacion_perfil = $id; }
    public function obtenerPorPersona($id_persona)
    {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.nombre_usuario,
                        u.cuenta_activada,
                        u.relacion_persona,
                        u.relacion_perfil,
                        p.nombre_perfil
                    FROM usuario u
                    LEFT JOIN perfil p ON u.relacion_perfil = p.id_perfil
                    WHERE u.relacion_persona = :id_persona";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) return null;

            $this->id_usuario = $user["id_usuario"];
            $this->nombre_usuario = $user["nombre_usuario"];
            $this->cuenta_activada = $user["cuenta_activada"];
            $this->relacion_persona = $user["relacion_persona"];
            $this->relacion_perfil = $user["relacion_perfil"];

            return [
                "id_usuario" => $user["id_usuario"],
                "nombre_usuario" => $user["nombre_usuario"],
                "cuenta_activada" => (bool)$user["cuenta_activada"],
                "relacion_perfil" => [
                    "id_perfil" => $user["relacion_perfil"],
                    "nombre_perfil" => $user["nombre_perfil"]
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en Usuario::obtenerPorPersona -> " . $e->getMessage());
            return null;
        }
    }
    public function insertarUsuario($nombre_usuario, $id_persona, $id_perfil)
    {
        try {
            $sql = "INSERT INTO usuario (nombre_usuario, cuenta_activada, relacion_persona, relacion_perfil)
                    VALUES (:nombre, 0, :persona, :perfil)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre_usuario, PDO::PARAM_STR);
            $stmt->bindParam(':persona', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':perfil', $id_perfil, PDO::PARAM_INT);
            $stmt->execute();

            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en Usuario::insertarUsuario -> " . $e->getMessage());
            return false;
        }
    }
    public function actualizarUsuario($id_usuario, $nombre_usuario, $id_perfil, $cuenta_activada)
    {
        try {
            $sql = "UPDATE usuario
                    SET nombre_usuario = :nombre,
                        relacion_perfil = :perfil,
                        cuenta_activada = :activo
                    WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre_usuario, PDO::PARAM_STR);
            $stmt->bindParam(':perfil', $id_perfil, PDO::PARAM_INT);
            $stmt->bindParam(':activo', $cuenta_activada, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }
    public function cambiarEstadoCuenta($id_usuario, $activo)
    {
        try {
            $sql = "UPDATE usuario SET cuenta_activada = :activo WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::cambiarEstadoCuenta -> " . $e->getMessage());
            return false;
        }
    }
    /**
     * Valida la fortaleza de una contraseña.
     * Retorna un array con 'valid' => bool y 'mensaje' => string.
     */
    public function validarPassword($password)
    {
        if (strlen($password) < 8)
            return ["valid" => false, "mensaje" => "Debe tener al menos 8 caracteres."];
        if (!preg_match("/[A-Z]/", $password))
            return ["valid" => false, "mensaje" => "Debe contener al menos una letra mayúscula."];
        if (!preg_match("/[a-z]/", $password))
            return ["valid" => false, "mensaje" => "Debe contener al menos una letra minúscula."];
        if (!preg_match("/\d/", $password))
            return ["valid" => false, "mensaje" => "Debe contener al menos un número."];
        if (!preg_match("/[\W_]/", $password))
            return ["valid" => false, "mensaje" => "Debe contener al menos un carácter especial."];
        
        return ["valid" => true, "mensaje" => "Contraseña válida."];
    }
    /**
     * Actualiza la contraseña de un usuario con hash seguro.
     */
    public function actualizarPassword($id_usuario, $nuevaPassword)
    {
        try {
            $validacion = $this->validarPassword($nuevaPassword);
            if (!$validacion["valid"]) return $validacion;

            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET password_usuario = :pass WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pass', $hash);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);

            $stmt->execute();
            return ["valid" => true, "mensaje" => "Contraseña actualizada correctamente."];
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarPassword -> " . $e->getMessage());
            return ["valid" => false, "mensaje" => "Error interno al actualizar la contraseña."];
        }
    }
    /**
     * Verifica si la contraseña ingresada coincide con la almacenada.
     */
    public function verificarPassword($id_usuario, $password)
    {
        try {
            $sql = "SELECT password_usuario FROM usuario WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return false;
            return password_verify($password, $data["password_usuario"]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::verificarPassword -> " . $e->getMessage());
            return false;
        }
    }
    // Método para obtener la información personal desde la sección mi perfil:
public function obtenerInformacionCompleta($id_usuario)
{
    try {
        $sql = "
            SELECT 
                u.id_usuario,
                u.nombre_usuario,
                DATE_FORMAT(u.fecha_registro, '%d/%m/%Y') AS fecha_registro,
                p.descripcion_perfil,

                CONCAT(per.nombre_persona, ' ', per.apellido_persona) AS nombre_completo,
                DATE_FORMAT(per.fecha_nac_persona, '%d/%m/%Y') AS fecha_nacimiento,
                IFNULL(g.nombre_genero, '-') AS genero,

                -- Dirección completa concatenada (país → provincia → localidad → barrio → calle)
                CONCAT_WS(', ',
                    IFNULL(d.calle_direccion, NULL),
                    IFNULL(CONCAT('N° ', d.numero_direccion), NULL),
                    IFNULL(CONCAT('Piso ', d.piso_direccion), NULL),
                    IFNULL(d.info_extra_direccion, NULL),
                    IFNULL(b.nombre_barrio, NULL),
                    IFNULL(l.nombre_localidad, NULL),
                    IFNULL(pr.nombre_provincia, NULL),
                    IFNULL(pa.nombre_pais, NULL)
                ) AS direccion_completa,

                -- Contacto (email / teléfono)
                IFNULL(tc.nombre_tipo_contacto, '-') AS tipo_contacto,
                IFNULL(dc.descripcion_contacto, '-') AS contacto,

                -- Documento (tipo + detalle)
                IFNULL(CONCAT(td.nombre_tipo_documento, ' ', dd.descripcion_documento), '-') AS documento

            FROM usuario u
            INNER JOIN perfil p ON u.relacion_perfil = p.id_perfil
            INNER JOIN persona per ON u.relacion_persona = per.id_persona
            LEFT JOIN genero g ON per.id_genero = g.id_genero
            LEFT JOIN domicilio d ON per.id_domicilio = d.id_domicilio
            LEFT JOIN barrio b ON d.id_barrio = b.id_barrio
            LEFT JOIN localidad l ON b.id_localidad = l.id_localidad
            LEFT JOIN provincia pr ON l.id_provincia = pr.id_provincia
            LEFT JOIN pais pa ON pr.id_pais = pa.id_pais
            LEFT JOIN detalle_contacto dc ON per.id_detalle_contacto = dc.id_detalle_contacto
            LEFT JOIN tipo_contacto tc ON dc.id_tipo_contacto = tc.id_tipo_contacto
            LEFT JOIN detalle_documento dd ON per.id_detalle_documento = dd.id_detalle_documento
            LEFT JOIN tipo_documento td ON dd.id_tipo_documento = td.id_tipo_documento
            WHERE u.id_usuario = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        return $datos ?: null;

    } catch (PDOException $e) {
        error_log("Error en Usuario::obtenerInformacionCompleta -> " . $e->getMessage());
        return null;
    }
}
    /**
     *  Obtiene los datos completos del usuario (incluyendo contraseña) por ID.
     * Se utiliza para validar la contraseña actual antes de actualizarla.
     */
    public function obtenerPorId($id_usuario)
    {
        try {
            $sql = "SELECT id_usuario, nombre_usuario, password_usuario 
                    FROM usuario 
                    WHERE id_usuario = :id 
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::obtenerPorId -> " . $e->getMessage());
            return null;
        }
    }
    /**
     * Actualiza la contraseña de un usuario con hash ya generado.
     * Retorna true si la actualización fue exitosa.
     */
/**
 * Actualiza la contraseña de un usuario y registra el cambio en auditoría.
 */
public function actualizarContrasena($id_usuario, $nuevaHash, $ipCambio = null)
{
    try {
        // Iniciar transacción
        $this->conn->beginTransaction();

        // Actualizar la contraseña
        $sql = "UPDATE usuario 
                SET password_usuario = :hash 
                WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hash', $nuevaHash, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        // Registrar en la tabla de auditoría
        $sqlAuditoria = "INSERT INTO auditoria_contrasenas (id_usuario, ip_cambio) 
                         VALUES (:id_usuario, :ip)";
        $stmtAud = $this->conn->prepare($sqlAuditoria);
        $stmtAud->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtAud->bindParam(':ip', $ipCambio, PDO::PARAM_STR);
        $stmtAud->execute();

        $this->conn->commit();
        return ["success" => true, "mensaje" => "Contraseña actualizada y registrada correctamente."];

    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Error en Usuario::actualizarContrasena -> " . $e->getMessage());
        return ["success" => false, "mensaje" => "Error al actualizar la contraseña."];
    }
}

}
?>
