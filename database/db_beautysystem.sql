/* ╔══════════════╗
   ║CREACIÓN DE BD║
   ╚══════════════╝ */
CREATE DATABASE beautysystem;
USE beautysystem;
/* ╔══════════════════════════╗
   ║GESTIÓN DE ESTADOS LÓGICOS║
   ╚══════════════════════════╝ */
CREATE TABLE estado_logico (
    id_estado_logico INT PRIMARY KEY AUTO_INCREMENT,
    nombre_estado VARCHAR(50)
);
/* ╔══════════════════════╗
   ║GESTIÓN DE DIRECCIONES║
   ╚══════════════════════╝ */
CREATE TABLE pais (
    id_pais INT PRIMARY KEY AUTO_INCREMENT,
    nombre_pais VARCHAR(50)
);
CREATE TABLE provincia (
    id_provincia INT PRIMARY KEY AUTO_INCREMENT,
    nombre_provincia VARCHAR(50),
    id_pais INT,
    FOREIGN KEY (id_pais) REFERENCES pais(id_pais)
);
CREATE TABLE localidad (
    id_localidad INT PRIMARY KEY AUTO_INCREMENT,
    nombre_localidad VARCHAR(50),
    id_provincia INT,
    FOREIGN KEY (id_provincia) REFERENCES provincia(id_provincia)
);
CREATE TABLE barrio (
    id_barrio INT PRIMARY KEY AUTO_INCREMENT,
    nombre_barrio VARCHAR(50),
    id_localidad INT,
    FOREIGN KEY (id_localidad) REFERENCES localidad(id_localidad)
);
CREATE TABLE domicilio (
    id_domicilio INT PRIMARY KEY AUTO_INCREMENT,
    calle_direccion VARCHAR(100),
    numero_direccion VARCHAR(10),
    piso_direccion VARCHAR(10),
    info_extra_direccion VARCHAR(100),
    id_barrio INT,
    FOREIGN KEY (id_barrio) REFERENCES barrio(id_barrio)
);
/* ╔══════════════════════════════════╗
   ║GESTIÓN DE DOCUMENTOS DE IDENTIDAD║
   ╚══════════════════════════════════╝ */
CREATE TABLE tipo_documento (
    id_tipo_documento INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo_documento VARCHAR(50)
);
CREATE TABLE detalle_documento (
    id_detalle_documento INT PRIMARY KEY AUTO_INCREMENT,
    id_tipo_documento INT,
    descripcion_documento VARCHAR(100),
    FOREIGN KEY (id_tipo_documento) REFERENCES tipo_documento(id_tipo_documento)
);
/* ╔══════════════════════════════════╗
   ║GESTIÓN DE INFORMACIÓN DE CONTACTO║
   ╚══════════════════════════════════╝ */
CREATE TABLE tipo_contacto (
    id_tipo_contacto INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo_contacto VARCHAR(50)
);
CREATE TABLE detalle_contacto (
    id_detalle_contacto INT PRIMARY KEY AUTO_INCREMENT,
    descripcion_contacto VARCHAR(100),
    id_tipo_contacto INT,
    FOREIGN KEY (id_tipo_contacto) REFERENCES tipo_contacto(id_tipo_contacto)
);
/* ╔═════════════════════════════════════════════╗
   ║GESTIÓN DE INFORMACIÓN DE IDENTIDAD DE GÉNERO║
   ╚═════════════════════════════════════════════╝ */
CREATE TABLE genero (
    id_genero INT PRIMARY KEY AUTO_INCREMENT,
    nombre_genero VARCHAR(50) NOT NULL UNIQUE
);
/* ╔══════════════════════════════════╗
   ║GESTIÓN DE INFORMACIÓN DE PERSONAL║
   ╚══════════════════════════════════╝ */
CREATE TABLE persona (
    id_persona INT PRIMARY KEY AUTO_INCREMENT,
    nombre_persona VARCHAR(50) NOT NULL,
    apellido_persona VARCHAR(50) NOT NULL,
    fecha_nac_persona DATE,
    id_genero INT,
    id_domicilio INT,
    id_detalle_documento INT,
    id_detalle_contacto INT,
    FOREIGN KEY (id_genero) REFERENCES genero(id_genero),
    FOREIGN KEY (id_domicilio) REFERENCES domicilio(id_domicilio),
    FOREIGN KEY (id_detalle_documento) REFERENCES detalle_documento(id_detalle_documento),
    FOREIGN KEY (id_detalle_contacto) REFERENCES detalle_contacto(id_detalle_contacto)
);
/* ╔═══════════════════╗
   ║GESTIÓN DE PERFILES║
   ╚═══════════════════╝ */
CREATE TABLE perfil (
    id_perfil INT PRIMARY KEY AUTO_INCREMENT,
    descripcion_perfil VARCHAR(50),
    activo_perfil TINYINT(1) DEFAULT 1
);
/* ╔══════════════════╗
   ║GESTIÓN DE MÓDULOS║
   ╚══════════════════╝ */
CREATE TABLE modulo (
    id_modulo INT PRIMARY KEY AUTO_INCREMENT,
    descripcion_modulo VARCHAR(100),
    activo_modulo TINYINT(1) DEFAULT 1
);
/* ╔═══════════════════╗
   ║GESTIÓN DE PERMISOS║
   ╚═══════════════════╝ */
CREATE TABLE modulo_perfil (
    relacion_modulo INT,
    relacion_perfil INT,
    PRIMARY KEY (relacion_modulo, relacion_perfil),
    FOREIGN KEY (relacion_modulo) REFERENCES modulo(id_modulo) ON DELETE CASCADE,
    FOREIGN KEY (relacion_perfil) REFERENCES perfil(id_perfil) ON DELETE CASCADE
);
/* ╔═══════════════════╗
   ║GESTIÓN DE USUARIOS║
   ╚═══════════════════╝ */
CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    password_usuario VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_usuario TINYINT(1) DEFAULT 0, -- 0 = Inactivo, 1 = Activo
    cuenta_activada TINYINT(1) DEFAULT 0,
    relacion_persona INT NOT NULL,
    relacion_perfil INT NOT NULL,
    FOREIGN KEY (relacion_persona) REFERENCES persona(id_persona),
    FOREIGN KEY (relacion_perfil) REFERENCES perfil(id_perfil)
);
/* ╔═══════════════════════════════════════════════════════════════╗
   ║  AUDITORÍA DE CAMBIOS DE CONTRASEÑA                           ║
   ╚═══════════════════════════════════════════════════════════════╝ */
CREATE TABLE auditoria_contrasenas (
    id_auditoria INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_cambio VARCHAR(45) NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

/* ╔═════════════════════════════════╗
   ║GESTIÓN DE TOKENS DE RECUPERACIÓN║
   ╚═════════════════════════════════╝ */
CREATE TABLE tokens_usuario (
    id_token INT PRIMARY KEY AUTO_INCREMENT,
    relacion_usuario INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    tipo ENUM('activacion', 'recuperacion') NOT NULL,
    expiracion DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relacion_usuario) REFERENCES usuario(id_usuario)
);
/* ╔═══════════════════╗
   ║GESTIÓN DE SESIONES║
   ╚═══════════════════╝ */
CREATE TABLE sesion (
    id_sesion INT PRIMARY KEY AUTO_INCREMENT,
    relacion_usuario INT NOT NULL,
    activa_sesion TINYINT(1) NOT NULL DEFAULT 0,
    fecha_ultimo_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relacion_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);
/* ╔═══════════════════╗
   ║GESTIÓN DE CLIENTES║
   ╚═══════════════════╝ */
CREATE TABLE cliente (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    estado_cliente TINYINT DEFAULT 1,
    relacion_persona INT NOT NULL,
    FOREIGN KEY (relacion_persona) REFERENCES persona(id_persona)
);
/* ╔════════════════════╗
   ║GESTIÓN DE EMPLEADOS║
   ╚════════════════════╝ */
CREATE TABLE empleado (
    id_empleado INT PRIMARY KEY AUTO_INCREMENT,
    relacion_persona INT NOT NULL,
    fecha_alta_empleado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_empleado TINYINT DEFAULT 1,
    FOREIGN KEY (relacion_persona) REFERENCES persona(id_persona)
);

/* ╔═════════════════════╗
   ║GESTIÓN DE REPARTIDOR║
   ╚═════════════════════╝ */
CREATE TABLE repartidor (
    id_repartidor INT PRIMARY KEY AUTO_INCREMENT,
    relacion_persona INT NOT NULL,
    fecha_alta_repartidor TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_repartidor TINYINT DEFAULT 1,
    FOREIGN KEY (relacion_persona) REFERENCES persona(id_persona)
);

/* ╔══════════════════════════════════╗
   ║GESTIÓN DE CATEGORÍAS DE PRODUCTOS║
   ╚══════════════════════════════════╝ */
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    imagen_categoria VARCHAR(255),
    alta_categoria TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizacion_categoria DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_estado_logico INT,
    FOREIGN KEY (id_estado_logico) REFERENCES estado_logico(id_estado_logico)
);
/* ╔════════════════════════════════════════════════════╗
   ║GESTIÓN DE SUB-CATEGORÍAS DE CATEGORÍAS DE PRODUCTOS║
   ╚════════════════════════════════════════════════════╝ */
CREATE TABLE sub_categoria (
    id_sub_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre_sub_categoria VARCHAR(50) NOT NULL,
    cant_sub_categoria INT NOT NULL,
    id_estado_logico INT,
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_estado_logico) REFERENCES estado_logico(id_estado_logico)
);
/* ╔══════════════════════════════╗
   ║GESTIÓN DE MARCAS DE PRODUCTOS║
   ╚══════════════════════════════╝ */
CREATE TABLE marca (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre_marca VARCHAR(100)
);
/* ╔══════════════════════════════════════════╗
   ║GESTIÓN DE UNIDADES DE MEDIDA DE PRODUCTOS║
   ╚══════════════════════════════════════════╝ */
CREATE TABLE unidad_medida (
    id_unidad_medida INT AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad_medida VARCHAR(100)
);
/* ╔════════════════════╗
   ║GESTIÓN DE PRODUCTOS║
   ╚════════════════════╝ */
CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(100) UNIQUE,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion_producto TEXT,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_minimo INT NOT NULL,
    stock_actual INT NOT NULL,
    imagen_producto VARCHAR(255),
    id_marca INT,
    id_categoria INT,
    id_sub_categoria INT,
    id_unidad_medida INT,
    id_estado_logico INT,
    alta_producto TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizacion_producto DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_marca) REFERENCES marca(id_marca),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_sub_categoria) REFERENCES sub_categoria(id_sub_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_unidad_medida) REFERENCES unidad_medida(id_unidad_medida),
    FOREIGN KEY (id_estado_logico) REFERENCES estado_logico(id_estado_logico)
);
/* ╔══════════════════════════╗
   ║GESTIÓN DE MÉTODOS DE PAGO║
   ╚══════════════════════════╝ */
CREATE TABLE metodo_pago (
    id_metodo_pago INT AUTO_INCREMENT PRIMARY KEY,
    nombre_metodo_pago VARCHAR(50)
);
/* ╔═════════════════════════════════════════════╗
   ║GESTIÓN DE EMISIÓN DE NOTAS PARA DEVOLUCIONES║
   ╚═════════════════════════════════════════════╝ */
CREATE TABLE tipo_nota (
    id_tipo_nota INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo_nota VARCHAR(50)
);
/* ╔═════════════════╗
   ║GESTIÓN DE BANNER║
   ╚═════════════════╝ */
CREATE TABLE banner (
    id_banner INT AUTO_INCREMENT PRIMARY KEY,
    titulo_banner TEXT NOT NULL,
    imagen_banner VARCHAR(255) NOT NULL,
    estado_banner TINYINT(1) DEFAULT 1
);
/* ╔══════════════════════════════╗
   ║GESTIÓN DE PEDIDOS DE CLIENTES║
   ╚══════════════════════════════╝ */
CREATE TABLE pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_estado_logico INT,
    id_repartidor INT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto_total DECIMAL(10,2),
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_estado_logico) REFERENCES estado_logico(id_estado_logico),
    FOREIGN KEY (id_repartidor) REFERENCES repartidor(id_repartidor) ON DELETE SET NULL ON UPDATE CASCADE
);
/* ╔══════════════════════════════════════════╗
   ║GESTIÓN DE DETALLES DEL PEDIDO DE CLIENTES║
   ╚══════════════════════════════════════════╝ */
CREATE TABLE detalle_pedido (
    id_detalle_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_producto INT,
    cantidad_producto INT NOT NULL,
    precio_unitario DECIMAL(10,2),
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
);
/* ╔════════════════════════════╗
   ║GESTIÓN DE PAGO DE PRODUCTOS║
   ╚════════════════════════════╝ */
CREATE TABLE pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_metodo_pago INT,
    estado_pago ENUM('pendiente', 'completado', 'fallido') NOT NULL,
    monto_pago DECIMAL(10,2),
    payment_mp_id VARCHAR(100),
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_metodo_pago) REFERENCES metodo_pago(id_metodo_pago)
);
/* ╔════════════════════════════════════════════╗
   ║GESTIÓN DE INFORMACIÓN DE ENVÍO DEL PRODUCTO║
   ╚════════════════════════════════════════════╝ */
CREATE TABLE envio (
    id_envio INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT,
    id_domicilio INT NULL,
    estado ENUM('pendiente', 'en camino', 'entregado') NOT NULL,
    fecha_envio TIMESTAMP NULL,
    fecha_entrega TIMESTAMP NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_domicilio) REFERENCES domicilio(id_domicilio)
);
/* ╔════════════════════════════════════════════╗
   ║GESTIÓN DE LISTAS DE DESEOS DE LOS CLIENTES ║
   ╚════════════════════════════════════════════╝ */
CREATE TABLE wishlist (
    id_wishlist INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_producto INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
);
/* ╔══════════════════════════════════════════════╗
   ║GESTIÓN DE RESEÑAS Y VALORACIONES DE PRODUCTOS║
   ╚══════════════════════════════════════════════╝ */
CREATE TABLE review (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_producto INT,
    calificacion INT CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_review TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
);
/* ╔════════════════════════════════════════════════╗
   ║GESTIÓN DE PUBLICACIONES DEL BLOG ADMINISTRATIVO║
   ╚════════════════════════════════════════════════╝ */
CREATE TABLE blog_post (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL, 
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);
/* ╔═════════════════════════════════════════════╗
   ║GESTIÓN DE COMENTARIOS DE CLIENTES EN EL BLOG║
   ╚═════════════════════════════════════════════╝ */
CREATE TABLE comentarios_blog (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT,
    id_cliente INT,
    comentario TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_post) REFERENCES blog_post(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE
);
/* ╔════════════════════════════════════════════╗
   ║GESTIÓN DE NOTIFICACIONES PARA LOS USUARIOS ║
   ╚════════════════════════════════════════════╝ */
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    mensaje TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_estado_logico INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_estado_logico) REFERENCES estado_logico(id_estado_logico)
);
/* ╔═══════════════════════════════╗
   ║GESTIÓN DE COMPRAS DE CLIENTES ║
   ╚═══════════════════════════════╝ */
CREATE TABLE historial_compra (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_pedido INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE
);
/* ╔══════════════════════════════════╗
   ║GESTIÓN DE PERÍODOS PARA REPORTES ║
   ╚══════════════════════════════════╝ */
CREATE TABLE periodo (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_periodo VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    descripcion TEXT NULL,
    cantidad_vendida INT NULL
);
/* ╔══════════════════════════════════════════════╗
   ║GESTIÓN DE PERÍODOS PARA REPORTES (RELACIÓN)  ║
   ╚══════════════════════════════════════════════╝ */
CREATE TABLE periodo_pedido (
    id_periodo_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_periodo INT NOT NULL,
    id_pedido INT NOT NULL,
    FOREIGN KEY (id_periodo) REFERENCES periodo(id_periodo),
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
);
/* ╔══════════════════════╗
   ║GESTIÓN DE AUDITORÍA  ║
   ╚══════════════════════╝ */
CREATE TABLE auditoria (
    id_auditoria       INT AUTO_INCREMENT PRIMARY KEY,
    tabla_afectada     VARCHAR(100) NOT NULL,
    id_registro        INT NULL,
    accion             ENUM('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT') NOT NULL,
    datos_antes        JSON NULL,
    datos_despues      JSON NULL,
    usuario_id         INT NULL,
    detalle            JSON NULL,
    ip_origen          VARCHAR(45) NULL,
    user_agent         VARCHAR(255) NULL,
    fecha_evento       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id_usuario)
);

CREATE TABLE auditoria_permisos (
    id_auditoria_permiso INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario_admin INT NOT NULL,  -- quién hizo la acción
    id_perfil_afectado INT NOT NULL,
    id_modulo_afectado INT NOT NULL,
    accion ENUM('ASIGNAR', 'REVOCAR') NOT NULL,
    fecha_evento DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_origen VARCHAR(45) NULL,
    FOREIGN KEY (id_usuario_admin) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_perfil_afectado) REFERENCES perfil(id_perfil),
    FOREIGN KEY (id_modulo_afectado) REFERENCES modulo(id_modulo)
);

/* ╔═══════════════════════════════════════════════════════════════╗
   ║  TRIGGER AUTOMÁTICO DE AUDITORÍA                              ║
   ╚═══════════════════════════════════════════════════════════════╝ */
DELIMITER $$

CREATE TRIGGER trg_auditoria_cambio_password
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    -- Solo registrar si el password realmente cambió
    IF (OLD.password_usuario <> NEW.password_usuario) THEN
        INSERT INTO auditoria_contrasenas (id_usuario, fecha_cambio)
        VALUES (NEW.id_usuario, NOW());
    END IF;
END $$

DELIMITER ;
DELIMITER $$

CREATE TRIGGER trg_envio_marcar_en_camino
AFTER UPDATE ON envio
FOR EACH ROW
BEGIN
    -- Solo ejecutar si el estado realmente cambió a 'en camino'
    IF NEW.estado = 'en camino' AND OLD.estado <> 'en camino' THEN

        -- Actualizar el estado del pedido a 'Enviado' usando un ID existente
        UPDATE pedido
        SET id_estado_logico = (
            SELECT MIN(id_estado_logico)
            FROM estado_logico
            WHERE nombre_estado = 'Enviado'
        )
        WHERE id_pedido = NEW.id_pedido;

    END IF;
END $$

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_actualizar_cantidad_vendida
AFTER INSERT ON periodo_pedido
FOR EACH ROW
BEGIN
    UPDATE periodo
    SET cantidad_vendida = (
        SELECT COALESCE(SUM(dp.cantidad_producto), 0)
        FROM periodo_pedido pp
        INNER JOIN detalle_pedido dp 
            ON dp.id_pedido = pp.id_pedido
        WHERE pp.id_periodo = NEW.id_periodo
    )
    WHERE id_periodo = NEW.id_periodo;
END //

DELIMITER ;

/* ╔══════════════════════════════════════════╗
   ║INSERCIÓN DE DATOS BASE DE ESTADOS LÓGICOS║
   ╚══════════════════════════════════════════╝ */
INSERT INTO estado_logico (nombre_estado) VALUES 
-- Estados para bajas lógicas:
('Activo'), ('Inactivo'),
-- Estados para procesos de pago:
('Pagado'), ('Pendiente de pago'), 
-- Estados para el proceso de envío y recepción de un producto:
('Enviado'), ('Entregado'), ('Cancelado'),
-- Estados para el stock: 
('Disponible'), ('No disponible'), ('Agotado'),
-- Estados para el pedido:
('Pendiente'), ('Procesando');
-- Pendiente: pedido generado pero no pagado; procesando: preparando pedido; 
-- enviado: salió del depósito; entregado; proceso exitoso
-- Cancelado: (por el cliente o el sistema) 

/* ╔═════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE UBICACIONES║
   ╚═════════════════════════════════╝ */
INSERT INTO pais (nombre_pais) VALUES ('Argentina');

INSERT INTO provincia (nombre_provincia, id_pais) VALUES ('Formosa', 1);

INSERT INTO localidad (nombre_localidad, id_provincia) VALUES 
('Formosa', 1), ('Pirané', 1), ('Pozo del Tigre', 1), ('Laishí', 1), 
('San Martín II', 1), ('Villa Dos Trece', 1), ('Villafañe', 1), ('Ramón Lista', 1), 
('Río Muerto', 1), ('Pilcomayo', 1), ('Gral Belgrano', 1), ('Pilagás', 1), 
('Matacos', 1), ('Bermejo', 1), ('Las Lomitas', 1), ('Guemes', 1);

INSERT INTO barrio (nombre_barrio, id_localidad) VALUES 
('Barrio La Pilar', 1), ('Barrio 2 de Abril', 1), ('Barrio 7 de Mayo', 1),
('Barrio Antenor Gauna', 1), ('Barrio Bernardino Rivadavia', 1),
('Barrio Centenario', 1), ('Barrio Coluccio', 1), ('Barrio Curé Cuá', 1),
('Barrio Divino Niño Jesús', 1), ('Barrio El Amanecer', 1), ('Barrio El Palmar', 1),
('Barrio El Pucú', 1), ('Barrio Eva Perón', 1), ('Barrio Guadalupe', 1),
('Barrio Independencia', 1), ('Barrio Irigoyen', 1), ('Barrio Juan Domingo Perón', 1),
('Barrio Juan Manuel de Rosas', 1), ('Barrio La Colonia', 1), ('Barrio La Lomita', 1),
('Barrio La Nueva Formosa', 1), ('Barrio La Paz', 1), ('Barrio Laguna Siam', 1),
('Barrio Las Orquídeas', 1), ('Barrio Lote 4', 1), ('Barrio Lote 111', 1),
('Barrio Lote 67', 1), ('Barrio Lote Rural 3 Bis', 1), ('Barrio Los Inmigrantes', 1),
('Barrio Los Naranjos', 1), ('Barrio Los Pinos', 1), ('Barrio Mariano Moreno', 1),
('Barrio Medalla Milagrosa', 1), ('Barrio Nanqom', 1), ('Barrio Nuestra Señora de Luján', 1),
('Barrio Parque Urbano', 1), ('Barrio República Argentina', 1), ('Barrio Ricardo Balbín', 1),
('Barrio San Agustín', 1), ('Barrio San Antonio', 1), ('Barrio San Carlos', 1),
('Barrio San Cayetano', 1), ('Barrio San Fernando', 1), ('Barrio San Francisco de Asís', 1),
('Barrio San José Obrero', 1), ('Barrio San Juan Bautista', 1), ('Barrio San Lorenzo', 1),
('Barrio San Miguel', 1), ('Barrio San Pedro', 1), ('Barrio San Roque', 1),
('Barrio Santa Rosa', 1), ('Barrio Sagrado Corazón', 1), ('Barrio Sagrado Corazón de María', 1),
('Barrio Simón Bolívar', 1), ('Barrio Timbó', 1), ('Barrio Urunday', 1),
('Barrio Veinticinco de Mayo', 1), ('Barrio Venezuela', 1), ('Barrio Vial', 1),
('Barrio Villa Hermosa', 1), ('Barrio Villa Lourdes', 1), ('Barrio Villa Mabel', 1),
('Barrio Villa del Carmen', 1);

/* ╔═════════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE CONFIGURACIONES║
   ╚═════════════════════════════════════╝ */
INSERT INTO tipo_documento (nombre_tipo_documento) VALUES ('DNI'), ('CDI'), ('CUIT'), ('CUIL'), ('DNIe'), ('LC');

INSERT INTO tipo_contacto (nombre_tipo_contacto) VALUES ('Correo electrónico'), ('Número de teléfono');

INSERT INTO genero (nombre_genero) VALUES ('Masculino'), ('Femenino'), ('No binario'), ('Prefiero no decirlo');

/* ╔════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE CATEGORÍAS║
   ╚════════════════════════════════╝ */
INSERT INTO categoria (nombre_categoria, imagen_categoria, id_estado_logico) VALUES
('Skincare', NULL, 1),
('Maquillaje', NULL, 1),
('Brochas y pinceles', NULL, 1);

INSERT INTO sub_categoria (nombre_sub_categoria, cant_sub_categoria, id_estado_logico, id_categoria) VALUES
-- SKINCARE
('Limpieza Facial', 10, 1, 1),
('Tónicos', 8, 1, 1),
('Hidratantes', 12, 1, 1),
('Exfoliantes', 6, 1, 1),
('Mascarillas', 7, 1, 1),

-- MAQUILLAJE
('Base de Maquillaje', 15, 1, 2),
('Polvo Compacto', 10, 1, 2),
('Rubor', 9, 1, 2),
('Iluminador', 8, 1, 2),
('Rímel para Ojos', 12, 1, 2),
('Labial', 20, 1, 2),
('Lápiz para Cejas', 10, 1, 2),

-- BROCHAS Y PINCELES
('Brochas para Rostro', 10, 1, 3),
('Brochas para Ojos', 12, 1, 3),
('Pinceles para Labios', 8, 1, 3),
('Esponjas de Maquillaje', 6, 1, 3);

/* ╔════════════════════════════╗
   ║INSERCIÓN DE DATOS DE MARCAS║
   ╚════════════════════════════╝ */
INSERT INTO marca (nombre_marca) VALUES 
('MAC'), ('Maybelline'), ('NARS'), ('Fenty Beauty'), ('Urban Decay'), ('L’Oréal'),
('Dior'), ('The Ordinary'), ('La Roche-Posay'), ('CeraVe'), ('Neutrogena'), ('Clinique'), ('Estée Lauder');

/* ╔══════════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE UNIDADES Y PAGOS║
   ╚══════════════════════════════════════╝ */
INSERT INTO unidad_medida (nombre_unidad_medida) VALUES 
('ml'), ('oz'), ('gr'), ('lt'), ('unidades'), ('pieza'), ('pack'), ('kg'), ('sobre');

INSERT INTO metodo_pago (nombre_metodo_pago) VALUES ('Efectivo'), ('Tarjeta débito'), ('Tarjeta crédito'), ('Transferencia');

INSERT INTO tipo_nota (nombre_tipo_nota) VALUES ('Nota de crédito'), ('Nota de débito');

/* ╔═════════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE PERFILES DE USO║
   ╚═════════════════════════════════════╝ */
INSERT INTO perfil (descripcion_perfil) VALUES 
('Administrador'),   
('Empleado'),        
('Repartidor'),      
('Cliente'),          
('Invitado');         

/* ╔═════════════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE MÓDULOS DEL SISTEMA║
   ╚═════════════════════════════════════════╝ */
INSERT INTO modulo (descripcion_modulo) VALUES 
('Catálogo'), 
('Usuarios'), 
('Clientes'), 
('Ventas'), 
('Inventario'), 
('Productos'),
('Pedidos'),
('Configuración'), 
('Blog interno'), 
('Reportes'),
('Cósmeticos'), 
('Blog externo'), 
('Sobre nosotros'),
('Home'),
('Cliente para clientes'),
('Audit'); 

/* ╔════════════════════════════════╗
   ║ASIGNACIÓN DE MÓDULOS POR PERFIL║
   ╚════════════════════════════════╝ */
-- ADMINISTRADOR (acceso total)
INSERT INTO modulo_perfil (relacion_modulo, relacion_perfil) VALUES
(3, 1), (4, 1), (5, 1), (8, 1), (9, 1), (10, 1), (16, 1);

-- EMPLEADO (acceso parcial)
INSERT INTO modulo_perfil (relacion_modulo, relacion_perfil) VALUES
(1, 2), (3, 2), (4, 2), (5, 2), (6, 2), (7, 2), (9, 2);

-- REPARTIDOR (solo pedidos)
INSERT INTO modulo_perfil (relacion_modulo, relacion_perfil) VALUES (14, 3), (7, 3);

-- CLIENTE (catálogo, pedidos y blog)
INSERT INTO modulo_perfil (relacion_modulo, relacion_perfil) VALUES (7, 4), (11, 4), 
(12, 4), (13, 4), (14, 4), (15, 4);

-- INVITADO (acceso externo: cósmeticos, blog externo y sobre nosotros)
INSERT INTO modulo_perfil (relacion_modulo, relacion_perfil) VALUES (11, 5), (12, 5), (13, 5), (14, 5);
/* ╔═══════════════════════════════════════════════════════════════════════╗
   ║      INSERCIÓN DE USUARIOS BASE DEL SISTEMA CON DATOS ASOCIADOS       ║
   ╚═══════════════════════════════════════════════════════════════════════╝ */

START TRANSACTION;

/* ╔══════════════════════════════════════════════════╗
   ║USUARIO / PERSONA 1 → PERFIL ADMINISTRADOR (ID=1) ║
   ╚══════════════════════════════════════════════════╝ */
-- Domicilio (id_domicilio = 1)
INSERT INTO domicilio (calle_direccion, numero_direccion, piso_direccion, info_extra_direccion, id_barrio)
VALUES ('Carlos Girola', '315', NULL, NULL, 1);

-- Documento (id_detalle_documento = 1)
INSERT INTO detalle_documento (descripcion_documento, id_tipo_documento)
VALUES ('44675392', 1);

-- Contactos (id_detalle_contacto = 1 y 2)
INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('adminbeauty@gmail.com', 1);
INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704784530', 2);

-- Persona (id_persona = 1)
INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Miranda', 'Fernandez', '2000-12-20', 1, 1, 1);

-- Usuario (perfil 1 = administrador)
INSERT INTO usuario (
    nombre_usuario, password_usuario, relacion_persona, relacion_perfil,
    estado_usuario, cuenta_activada
)
VALUES (
    'admin_beauty', 
    '$2y$10$G4ZVKwC.WJw7N8CS2b87.elXRzaDWe55d1/Kq.JUeeGRdzH3A5Xly', -- Contraseña = admin1234
    1, 1,
    1, 1
);

/* ╔═══════════════════════════════════════════════╗
   ║USUARIO / PERSONA 2 → PERFIL EMPLEADO (ID=2)   ║
   ╚═══════════════════════════════════════════════╝ */
-- Domicilio (id_domicilio = 2)
INSERT INTO domicilio (calle_direccion, numero_direccion, piso_direccion, info_extra_direccion, id_barrio)
VALUES ('Almafuerte', '789', NULL, 'Depto 5A', 3);

-- Documento (id_detalle_documento = 2)
INSERT INTO detalle_documento (descripcion_documento, id_tipo_documento)
VALUES ('44156607', 1);

-- Contacto (id_detalle_contacto = 3)
INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('juan.guzman@example.com', 1);

-- Persona (id_persona = 2)
INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Juan', 'Guzmán', '1990-02-20', 2, 2, 3);

-- Usuario (perfil 2 = empleado)
INSERT INTO usuario (
    nombre_usuario, password_usuario, relacion_persona, relacion_perfil,
    estado_usuario, cuenta_activada
)
VALUES (
    'juan_guzman',
    '$2y$10$zqpc295.bz.XHA.SAXLsn.utJaFHfZ0NLBPBSgXN5ggd601ttlyFG', -- Contraseña = empleado123
    2, 2,
    1, 1
);

/* ╔══════════════════════════════════════════════════════════════════════╗
   ║SUSTITUÍ EL ID=2 POR ID=1 PARA QUE EL ADMIN CUMPLA EL ROL DE EMPLEADO ║
   ╚══════════════════════════════════════════════════════════════════════╝ */
INSERT INTO empleado (relacion_persona)
VALUES (1);

/* ╔═══════════════════════════════════════════════╗
   ║USUARIO / PERSONA 3 → PERFIL REPARTIDOR (ID=3) ║
   ╚═══════════════════════════════════════════════╝ */
-- Domicilio (id_domicilio = 3)
INSERT INTO domicilio (calle_direccion, numero_direccion, piso_direccion, info_extra_direccion, id_barrio)
VALUES ('Mitre', '55', NULL, NULL, 4);

-- Documento (id_detalle_documento = 3)
INSERT INTO detalle_documento (descripcion_documento, id_tipo_documento)
VALUES ('42563044', 1);

-- Contacto (id_detalle_contacto = 4)
INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('luis.reparto@example.com', 1);

-- Persona (id_persona = 3)
INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Luis', 'García', '1992-08-10', 3, 3, 4);

-- Usuario (perfil 3 = repartidor)
INSERT INTO usuario (
    nombre_usuario, password_usuario, relacion_persona, relacion_perfil,
    estado_usuario, cuenta_activada
)
VALUES (
    'luis_repartidor',
    '$2y$10$OvOldM6iDhXVX8ebCuODqefoU3ofZuvkHaRPYpvBKwa8M3X86A.r6', -- Contraseña = repartidor123
    3, 3,
    1, 1
);

INSERT INTO repartidor (relacion_persona)
VALUES (3);

/* ╔═══════════════════════════════════════════════╗
   ║USUARIO / PERSONA 4 → PERFIL CLIENTE (ID=4)    ║
   ╚═══════════════════════════════════════════════╝ */
-- Domicilio (id_domicilio = 4)
INSERT INTO domicilio (calle_direccion, numero_direccion, piso_direccion, info_extra_direccion, id_barrio)
VALUES ('Oliva', '496', NULL, NULL, 2);

-- Documento (id_detalle_documento = 4)
INSERT INTO detalle_documento (descripcion_documento, id_tipo_documento)
VALUES ('32849531', 1);

-- Contacto (id_detalle_contacto = 5)
INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('sofia.cliente@example.com', 1);

-- Persona (id_persona = 4)
INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Sofía', 'Alarcón', '1990-05-20', 4, 4, 5);

-- Usuario (perfil 4 = cliente)
-- Contraseña en texto plano: Cliente#2025
INSERT INTO usuario (
    nombre_usuario, password_usuario, relacion_persona, relacion_perfil,
    estado_usuario, cuenta_activada
)
VALUES (
    'sofia_cliente',
    '$2y$10$tFjkHlLbrnnLcs9B4vy.q.BNLc07Ct7YND0dcXv5Kqqii/bkPynOG', -- Contraseña = cliente123
    4, 4,
    1, 1
);

INSERT INTO cliente (relacion_persona)
VALUES (4);

/* ╔══════════════════════════════════════╗
   ║INSERCIÓN DE DATOS DE PRODUCTOS (20)  ║
   ╚══════════════════════════════════════╝ */
INSERT INTO producto (
    codigo_barras,
    nombre_producto,
    descripcion_producto,
    precio_compra,
    precio_venta,
    stock_minimo,
    stock_actual,
    imagen_producto,
    id_marca,
    id_categoria,
    id_sub_categoria,
    id_unidad_medida,
    id_estado_logico
) VALUES
-- SKINCARE
('SK001', 'Espuma Limpiadora Suave', 'Espuma facial suave que elimina impurezas sin resecar.', 10.00, 18.00, 5, 40, NULL, 10, 1, 1, 1, 8),
('SK002', 'Tónico Refrescante Hidratante', 'Refresca y tonifica la piel preparándola para la hidratación.', 8.00, 15.00, 5, 35, NULL, 9, 1, 2, 1, 8),
('SK003', 'Crema Hidratante con Ácido Hialurónico', 'Hidrata profundamente y mejora la elasticidad de la piel.', 12.00, 22.00, 3, 28, NULL, 10, 1, 3, 1, 8),
('SK004', 'Exfoliante Facial Suave', 'Exfoliante con microgránulos naturales para una piel radiante.', 9.00, 17.00, 4, 25, NULL, 11, 1, 4, 3, 8),
('SK005', 'Mascarilla Purificante de Arcilla', 'Elimina el exceso de grasa y limpia los poros en profundidad.', 7.00, 14.00, 5, 20, NULL, 9, 1, 5, 3, 8),

-- MAQUILLAJE
('MK001', 'Base de Maquillaje Líquida Tono Claro', 'Cubre imperfecciones y deja un acabado natural.', 15.00, 28.00, 5, 30, NULL, 1, 2, 6, 1, 8),
('MK002', 'Polvo Compacto Mate', 'Fija el maquillaje y controla el brillo.', 10.00, 19.00, 4, 25, NULL, 2, 2, 7, 5, 8),
('MK003', 'Rubor en Polvo Rosa', 'Añade un toque de color saludable a tus mejillas.', 8.00, 16.00, 5, 22, NULL, 3, 2, 8, 3, 8),
('MK004', 'Iluminador en Polvo Dorado', 'Ilumina el rostro con un brillo sutil y natural.', 12.00, 23.00, 3, 20, NULL, 4, 2, 9, 3, 8),
('MK005', 'Rímel Volumen Extremo', 'Aporta volumen y longitud a las pestañas.', 9.00, 18.00, 5, 35, NULL, 2, 2, 10, 5, 8),
('MK006', 'Labial Mate Rojo Intenso', 'Color duradero y textura suave.', 6.00, 12.00, 5, 40, NULL, 1, 2, 11, 5, 8),
('MK007', 'Lápiz para Cejas Marrón', 'Define y rellena las cejas de manera natural.', 4.00, 9.00, 4, 30, NULL, 6, 2, 12, 5, 8),

-- BROCHAS Y PINCELES
('BR001', 'Brocha Kabuki para Base', 'Brocha densa ideal para aplicar base líquida o en polvo.', 5.00, 10.00, 3, 25, NULL, 5, 3, 13, 6, 8),
('BR002', 'Set de Brochas para Ojos 5pzs', 'Set profesional para sombras y delineado.', 8.00, 18.00, 4, 18, NULL, 5, 3, 14, 7, 8),
('BR003', 'Pincel Delineador Fino', 'Ideal para trazos precisos con gel o líquido.', 3.00, 7.00, 5, 30, NULL, 3, 3, 15, 6, 8),
('BR004', 'Esponja de Maquillaje Blender', 'Perfecta para aplicar base líquida o corrector.', 4.00, 9.00, 5, 40, NULL, 6, 3, 16, 6, 8),
('BR005', 'Brocha para Rubor Profesional', 'Brocha de cerdas suaves para aplicar rubor.', 5.50, 11.00, 4, 25, NULL, 5, 3, 13, 6, 8),

-- SKINCARE ADICIONALES
('SK006', 'Sérum Antioxidante con Vitamina C', 'Ilumina y unifica el tono de la piel.', 14.00, 27.00, 3, 22, NULL, 8, 1, 3, 1, 8),
('SK007', 'Aceite Facial Nutritivo', 'Aceite liviano con omega 3 y 6.', 13.00, 26.00, 3, 18, NULL, 12, 1, 3, 1, 8),
('SK008', 'Desmaquillante Bifásico', 'Elimina maquillaje resistente al agua.', 9.00, 17.00, 4, 25, NULL, 7, 1, 1, 1, 8),
('SK009', 'Mascarilla de Noche Revitalizante', 'Repara la piel durante el descanso nocturno.', 11.00, 22.00, 3, 20, NULL, 13, 1, 5, 1, 8);

/* ================================
   PEDIDO 1 – producto 1 (3 unidades)
   ================================ */
INSERT INTO pedido (id_cliente, id_estado_logico, monto_total)
VALUES (1, 12, 3 * 1500); -- ejemplo precio 1500

SET @id_pedido_1 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_1, 1, 3, 1500);

INSERT INTO envio (id_pedido, id_domicilio, estado)
VALUES (@id_pedido_1, 1, 'pendiente');


/* ================================
   PEDIDO 2 – producto 6 (10 unidades)
   ================================ */
INSERT INTO pedido (id_cliente, id_estado_logico, monto_total)
VALUES (1, 12, 10 * 2200); -- ejemplo precio 2200

SET @id_pedido_2 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_2, 6, 10, 2200);

INSERT INTO envio (id_pedido, id_domicilio, estado)
VALUES (@id_pedido_2, 1, 'pendiente');


/* ================================
   PEDIDO 3 – producto 4 (2 unidades)
   ================================ */
INSERT INTO pedido (id_cliente, id_estado_logico, monto_total)
VALUES (1, 12, 2 * 980);

SET @id_pedido_3 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_3, 4, 2, 980);

INSERT INTO envio (id_pedido, id_domicilio, estado)
VALUES (@id_pedido_3, 1, 'pendiente');


/* ================================
   PEDIDO 4 – producto 8 (5 unidades)
   ================================ */
INSERT INTO pedido (id_cliente, id_estado_logico, monto_total)
VALUES (1, 12, 5 * 3200);

SET @id_pedido_4 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_4, 8, 5, 3200);

INSERT INTO envio (id_pedido, id_domicilio, estado)
VALUES (@id_pedido_4, 1, 'pendiente');


/* ================================
   PEDIDO 5 – producto 3 (7 unidades)
   ================================ */
INSERT INTO pedido (id_cliente, id_estado_logico, monto_total)
VALUES (1, 12, 7 * 4500);

SET @id_pedido_5 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_5, 3, 7, 4500);

INSERT INTO envio (id_pedido, id_domicilio, estado)
VALUES (@id_pedido_5, 1, 'pendiente');

/* ================================
   INSERTAMOS pedido + historial de compra
   ================================ */
INSERT INTO historial_compra (id_cliente, id_pedido)
SELECT id_cliente, id_pedido
FROM pedido
WHERE id_pedido = 1 AND id_cliente = 1;

/* ╔══════════════════════════════════╗
   ║ INSERTAR PERÍODO FIN DE AÑO 2025 ║
   ╚══════════════════════════════════╝ */
INSERT INTO periodo (nombre_periodo, fecha_inicio, fecha_fin, cantidad_vendida)
VALUES ('Período fin de año 2025', '2025-11-01', '2025-12-31', NULL);

-- Obtener el ID real generado para este período
SET @id_periodo_1 = LAST_INSERT_ID();

/* ╔══════════════════════════╗
   ║ ASOCIAR PEDIDOS AL PERÍODO ║
   ╚══════════════════════════╝ */
/* ASOCIAR PEDIDOS AL PERÍODO */
INSERT INTO periodo_pedido (id_periodo, id_pedido) VALUES
(1, @id_pedido_1),
(1, @id_pedido_2),
(1, @id_pedido_3),
(1, @id_pedido_4),
(1, @id_pedido_5);

COMMIT;
/* ╔═══════════════════════════════════════════════════════════════════════╗
   ║      FIN DE INSERCIÓN DE USUARIOS BASE DEL SISTEMA (TRANSACCIÓN OK)   ║
   ╚═══════════════════════════════════════════════════════════════════════╝ */
START TRANSACTION;

-- ===========================
-- CLIENTE 2
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('San Martín', '1200', 15);
SET @id_domicilio_2 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '37894561');
SET @id_documento_2 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('carlos.perez@example.com', 1);
SET @id_contacto_email_2 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704888899', 2);
SET @id_contacto_tel_2 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Carlos', 'Pérez', '1992-07-22', 1, @id_domicilio_2, @id_documento_2, @id_contacto_email_2);
SET @id_persona_2 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_2, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('carlos_p', '$2y$10$g.MXZ6TSOXnUOYveS0Jq2u1boHJcpwk3eP8/Jtf3V3DQJjPK8L0V6', @id_persona_2, 4, 1, 1);


-- ===========================
-- CLIENTE 3
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Junín', '456', 5);
SET @id_domicilio_3 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '40123456');
SET @id_documento_3 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('florencia.mendez@example.com', 1);
SET @id_contacto_email_3 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704111122', 2);
SET @id_contacto_tel_3 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Florencia', 'Méndez', '1995-02-10', 2, @id_domicilio_3, @id_documento_3, @id_contacto_email_3);
SET @id_persona_3 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_3, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('flor_m', '$2y$10$78GkAvQiEatQRvxVOijOQOzgh7nvnK1oFkEdfHGDY0j0JfWK0J5Bm', @id_persona_3, 4, 1, 0);


-- ===========================
-- CLIENTE 4
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Las Heras', '721', 4);
SET @id_domicilio_4 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '39234567');
SET @id_documento_4 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('agustin.rojas@example.com', 1);
SET @id_contacto_email_4 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704222233', 2);
SET @id_contacto_tel_4 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Agustín', 'Rojas', '1988-12-02', 1, @id_domicilio_4, @id_documento_4, @id_contacto_email_4);
SET @id_persona_4 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_4, 2); -- BAJA LÓGICA

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('agus_r', '$2y$10$FOv1EeI5BVu30rbw.C2P0eOY9V16ReNJ/VBm40p4Si7iUdlX0D/E6', @id_persona_4, 4, 0, 0);


-- ===========================
-- CLIENTE 5
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Mitre', '1190', 6);
SET @id_domicilio_5 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '37654321');
SET @id_documento_5 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('lucia.varela@example.com', 1);
SET @id_contacto_email_5 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704664455', 2);
SET @id_contacto_tel_5 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Lucía', 'Varela', '1994-09-18', 2, @id_domicilio_5, @id_documento_5, @id_contacto_email_5);
SET @id_persona_5 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_5, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('lucia_v', '$2y$10$MQ904RzHlgdDUVfqERFZNuDbtQ5jYH2QjLNJ.GmXm8QOuLxsvp/lS', @id_persona_5, 4, 1, 1);



-- ===========================
-- CLIENTE 6
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Pringles', '870', 7);
SET @id_domicilio_6 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '41122334');
SET @id_documento_6 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('martin.carrizo@example.com', 1);
SET @id_contacto_email_6 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704669988', 2);
SET @id_contacto_tel_6 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Martín', 'Carrizo', '1996-05-11', 1, @id_domicilio_6, @id_documento_6, @id_contacto_email_6);
SET @id_persona_6 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_6, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('martinc', '$2y$10$v73tATY0bxoFbnGywF4nMuU5YgXOxYwCEfN2nJ/QQiZ1x28jQAuGu', @id_persona_6, 4, 1, 1);


-- ===========================
-- CLIENTE 7
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Rivadavia', '320', 12);
SET @id_domicilio_7 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '42566771');
SET @id_documento_7 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('andrea.bianchi@example.com', 1);
SET @id_contacto_email_7 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704778899', 2);
SET @id_contacto_tel_7 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Andrea', 'Bianchi', '1993-09-25', 2, @id_domicilio_7, @id_documento_7, @id_contacto_email_7);
SET @id_persona_7 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_7, 2); -- INACTIVO

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('andrea_b', '$2y$10$nLtD4Ee1WPHAvP0B56IpHuk8qn/hTAwhlNEpiSnb.MEa.Xm2iQrE2', @id_persona_7, 4, 0, 0);


-- ===========================
-- CLIENTE 8
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Eva Perón', '640', 18);
SET @id_domicilio_8 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '39886644');
SET @id_documento_8 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('roberto.silva@example.com', 1);
SET @id_contacto_email_8 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704998877', 2);
SET @id_contacto_tel_8 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Roberto', 'Silva', '1985-03-19', 1, @id_domicilio_8, @id_documento_8, @id_contacto_email_8);
SET @id_persona_8 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_8, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('roberto_s', '$2y$10$fPk80vM7IuQJj6Culibnz.YqTigPToIyXxsjy1bF.6GyIuEn1PiAu', @id_persona_8, 4, 1, 1);


-- ===========================
-- CLIENTE 9
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Padre Grotti', '300', 25);
SET @id_domicilio_9 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '41222334');
SET @id_documento_9 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('natalia.moreno@example.com', 1);
SET @id_contacto_email_9 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704995522', 2);
SET @id_contacto_tel_9 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Natalia', 'Moreno', '1991-11-04', 2, @id_domicilio_9, @id_documento_9, @id_contacto_email_9);
SET @id_persona_9 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_9, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('natalia_m', '$2y$10$4tiWZBQmKUWt8RWhhZhvAe9CoGfmdeypEQBBGDu2bBhIsiWr/Pc8C', @id_persona_9, 4, 1, 1);


-- ===========================
-- CLIENTE 10
-- ===========================
INSERT INTO domicilio (calle_direccion, numero_direccion, id_barrio)
VALUES ('Sarmiento', '890', 30);
SET @id_domicilio_10 = LAST_INSERT_ID();

INSERT INTO detalle_documento (id_tipo_documento, descripcion_documento)
VALUES (1, '45322119');
SET @id_documento_10 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('daniel.fernandez@example.com', 1);
SET @id_contacto_email_10 = LAST_INSERT_ID();

INSERT INTO detalle_contacto (descripcion_contacto, id_tipo_contacto)
VALUES ('3704663311', 2);
SET @id_contacto_tel_10 = LAST_INSERT_ID();

INSERT INTO persona (nombre_persona, apellido_persona, fecha_nac_persona,
    id_genero, id_domicilio, id_detalle_documento, id_detalle_contacto)
VALUES ('Daniel', 'Fernández', '1993-08-29', 1, @id_domicilio_10, @id_documento_10, @id_contacto_email_10);
SET @id_persona_10 = LAST_INSERT_ID();

INSERT INTO cliente (relacion_persona, estado_cliente) VALUES (@id_persona_10, 1);

INSERT INTO usuario (nombre_usuario, password_usuario, relacion_persona, relacion_perfil, 
    estado_usuario, cuenta_activada)
VALUES ('daniel_f', '$2y$10$rIMl.L4aOVC8xJXk41dtjuZLWIYX7wwqNugo1f4ETCKHVaEntgDqu', @id_persona_10, 4, 1, 1);


COMMIT;

START TRANSACTION;

-------------------------------------------
-- 📌 PEDIDOS PARA CLIENTE 2 (id_cliente = 2)
-------------------------------------------

-- Pedido 1 de cliente 2
INSERT INTO pedido (id_cliente, id_estado_logico, fecha_pedido, monto_total)
VALUES (2, 12, NOW(), (2 * 18000) + (1 * 9800));
SET @id_pedido_c2_1 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES 
(@id_pedido_c2_1, 1, 2, 18000),  -- Producto 1
(@id_pedido_c2_1, 4, 1, 9800);   -- Producto 4

INSERT INTO historial_compra (id_cliente, id_pedido)
VALUES (2, @id_pedido_c2_1);


-------------------------------------------
-- 📌 PEDIDOS PARA CLIENTE 3 (id_cliente = 3)
-------------------------------------------

-- Pedido 1 de cliente 3
INSERT INTO pedido (id_cliente, id_estado_logico, fecha_pedido, monto_total)
VALUES (3, 12, NOW(), (1 * 22500) + (3 * 4500));
SET @id_pedido_c3_1 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES 
(@id_pedido_c3_1, 6, 1, 22500),  -- Producto 6
(@id_pedido_c3_1, 3, 3, 4500);   -- Producto 3

INSERT INTO historial_compra (id_cliente, id_pedido)
VALUES (3, @id_pedido_c3_1);

-- Pedido 2 de cliente 3
INSERT INTO pedido (id_cliente, id_estado_logico, fecha_pedido, monto_total)
VALUES (3, 12, NOW(), (2 * 3200));
SET @id_pedido_c3_2 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_c3_2, 8, 2, 3200);

INSERT INTO historial_compra (id_cliente, id_pedido)
VALUES (3, @id_pedido_c3_2);


-------------------------------------------
-- 📌 PEDIDOS PARA CLIENTE 4 (id_cliente = 4)
-------------------------------------------

-- Pedido 1 de cliente 4
INSERT INTO pedido (id_cliente, id_estado_logico, fecha_pedido, monto_total)
VALUES (4, 12, NOW(), (1 * 28000));
SET @id_pedido_c4_1 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_c4_1, 11, 1, 28000);

INSERT INTO historial_compra (id_cliente, id_pedido)
VALUES (4, @id_pedido_c4_1);

-- Pedido 2 de cliente 4
INSERT INTO pedido (id_cliente, id_estado_logico, fecha_pedido, monto_total)
VALUES (4, 12, NOW(), (3 * 21000));
SET @id_pedido_c4_2 = LAST_INSERT_ID();

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad_producto, precio_unitario)
VALUES (@id_pedido_c4_2, 12, 3, 21000);

INSERT INTO historial_compra (id_cliente, id_pedido)
VALUES (4, @id_pedido_c4_2);

-- 🚚 ENVÍOS PARA CLIENTE 2
INSERT INTO envio (id_pedido, id_domicilio, estado, fecha_envio, fecha_entrega)
VALUES 
(@id_pedido_c2_1, NULL, 'pendiente', NULL, NULL);


-- 🚚 ENVÍOS PARA CLIENTE 3
INSERT INTO envio (id_pedido, id_domicilio, estado, fecha_envio, fecha_entrega)
VALUES 
(@id_pedido_c3_1, NULL, 'pendiente', NULL, NULL),
(@id_pedido_c3_2, NULL, 'pendiente', NULL, NULL);


-- 🚚 ENVÍOS PARA CLIENTE 4
INSERT INTO envio (id_pedido, id_domicilio, estado, fecha_envio, fecha_entrega)
VALUES 
(@id_pedido_c4_1, NULL, 'pendiente', NULL, NULL),
(@id_pedido_c4_2, NULL, 'pendiente', NULL, NULL);

INSERT INTO auditoria 
(tabla_afectada, accion, id_registro, datos_antes, datos_despues, usuario_id, detalle, ip_origen, user_agent)
VALUES
('producto', 'INSERT', 25, NULL,
'{"nombre":"Shampoo Natural","precio":1500,"stock":30,"categoria":"Cosmética"}',
1, NULL, '192.168.1.10', 'Mozilla/5.0');


INSERT INTO auditoria 
(tabla_afectada, accion, id_registro, datos_antes, datos_despues, usuario_id, detalle, ip_origen, user_agent)
VALUES
('producto', 'UPDATE', 25,
'{"nombre":"Shampoo Natural","precio":1500,"stock":30}',
'{"nombre":"Shampoo Natural Plus","precio":1800,"stock":25}',
1, NULL, '192.168.1.10', 'Mozilla/5.0');


INSERT INTO auditoria
(tabla_afectada, accion, id_registro, datos_antes, datos_despues, usuario_id, detalle, ip_origen, user_agent)
VALUES
('producto', 'DELETE', 25,
'{"nombre":"Shampoo Natural Plus","precio":1800,"stock":25}',
NULL,
1, NULL, '192.168.1.10', 'Mozilla/5.0');

INSERT INTO blog_post (id_usuario, titulo, contenido) VALUES
(1, 'Tendencias de maquillaje 2025', 
 'El estilo metálico, los labios glossy y las cejas naturales dominarán las pasarelas del 2025. El maquillaje se vuelve más luminoso y menos estructurado, apostando al brillo y a la piel real.'),

(3, 'Cómo cuidar tu piel en invierno', 
 'La piel sufre el frío, la calefacción y el viento. La clave está en la hidratación profunda, el uso de ceramidas y la protección solar incluso en días nublados.'),

(5, 'Skincare minimalista: ¿menos es más?', 
 'La piel agradece rutinas simples. Limpieza suave, hidratante liviano y protector solar. El exceso de productos puede dañar la barrera cutánea.'),

(7, 'Los mejores productos cruelty-free del mercado', 
 'Cada vez más marcas se suman al movimiento cruelty-free. Analizamos calidad, precio y dónde conseguir productos que respeten a los animales.'),

(9, '¿Qué maquillaje usar según tu tono de piel?', 
 'Identificar si tu piel es cálida, fría o neutra importa. Explicamos cómo elegir bases, rubores y labiales según tu tono y subtono.');

INSERT INTO comentarios_blog (id_post, id_cliente, comentario) VALUES
-- Post 1
(1, 2, 'Me encantaron las tendencias, especialmente el estilo luminoso.'),
(1, 4, 'Qué bueno que vuelve el brillo, ¡lo extrañaba!'),
(1, 7, '¿Podrían recomendar marcas accesibles?'),

-- Post 2
(2, 1, 'Muy buen artículo, no sabía que había que usar protector en invierno.'),
(2, 8, 'Yo uso ceramidas y me funcionó excelente.'),
(2, 3, '¿Alguna marca recomendada para piel seca?'),

-- Post 3
(3, 6, 'Por fin alguien que dice la verdad: menos productos es mejor.'),
(3, 2, 'Probé rutinas largas y mi piel empeoró.'),
(3, 9, '¿Cuál hidratante liviano me recomiendan?'),

-- Post 4
(4, 5, 'Gracias por la info, no conocía tantas marcas cruelty-free.'),
(4, 10, 'Excelente análisis de calidad y precio.'),
(4, 1, '¿Harán un post sobre maquillaje vegano también?'),

-- Post 5
(5, 3, 'Muy claro lo del subtono de piel, nunca lo entendía.'),
(5, 7, '¿Cómo saber mi subtono si no tengo maquillaje en casa?'),
(5, 4, 'Me gustaría ver ejemplos visuales de tonos cálidos y fríos.');


COMMIT;

