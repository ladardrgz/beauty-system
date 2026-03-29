<?php
require_once 'models/EnvioModel.php';

class PedidoModel
{
    private $db;
    private $envioModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->envioModel = new EnvioModel($db);
    }

    // =================================================================================
    // 1) CREAR PEDIDO COMPLETO DESDE EL CARRITO EN SESIÓN
    // =================================================================================
    /**
     * Crea un pedido usando el carrito guardado en $_SESSION['carrito'],
     * revalida stock, calcula el total, inserta pedido, detalles,
     * descuenta stock y genera el envío según la selección temporal.
     */
    public function crearPedidoDesdeSesion(int $idUsuario): array
    {
        Sesion::iniciar();

        // Obtener carrito real desde sesión
        $carrito = $_SESSION['carrito'] ?? [];
        if (empty($carrito)) {
            return ['success' => false, 'message' => 'El carrito está vacío.'];
        }

        try {
            $this->db->beginTransaction();

            // Obtener cliente a partir del usuario
            $idCliente = $this->obtenerClienteIdPorUsuario($idUsuario);
            if (!$idCliente) {
                throw new Exception('No se encontró cliente asociado al usuario actual.');
            }

            // Revalidar stock y calcular total
            $lineas = [];
            $montoTotal = 0;

            foreach ($carrito as $item) {
                if (!isset($item['id_producto'], $item['cantidad'])) {
                    continue;
                }

                $idProducto = (int)$item['id_producto'];
                $cantidad   = (int)$item['cantidad'];

                $producto = $this->obtenerProductoParaPedido($idProducto, true);
                if (!$producto) {
                    throw new Exception("Producto ID {$idProducto} no encontrado.");
                }

                if ($producto['stock_actual'] < $cantidad) {
                    throw new Exception(
                        "Stock insuficiente para '{$producto['nombre_producto']}'."
                    );
                }

                $subtotal = $producto['precio_venta'] * $cantidad;
                $montoTotal += $subtotal;

                $lineas[] = [
                    'id_producto'     => $idProducto,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $producto['precio_venta']
                ];
            }

            if (empty($lineas)) {
                throw new Exception('No hay productos válidos en el carrito.');
            }

            // Agregar costo de envío si corresponde
            $seleccionEnvio = $this->envioModel->obtenerSeleccionTemporal();
            if ($seleccionEnvio && $seleccionEnvio['tipo_envio'] === 'domicilio') {
                $montoTotal += 2000;
            }

            // Insertar el pedido (estado lógico: Pendiente)
            // Si quisieras hacerlo dinámico: $estadoPendiente = $this->obtenerIdEstadoPorNombre('Pendiente');
            $estadoPendiente = 11;

            $sqlPedido = "INSERT INTO pedido (id_cliente, id_estado_logico, monto_total, fecha_pedido)
                          VALUES (:id_cliente, :id_estado_logico, :monto_total, NOW())";
            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->bindParam(':id_cliente', $idCliente, PDO::PARAM_INT);
            $stmtPedido->bindParam(':id_estado_logico', $estadoPendiente, PDO::PARAM_INT);
            $stmtPedido->bindParam(':monto_total', $montoTotal);
            $stmtPedido->execute();

            $idPedido = (int)$this->db->lastInsertId();

            // Insertar detalles y descontar stock
            $sqlDetalle = "INSERT INTO detalle_pedido
                           (id_pedido, id_producto, cantidad_producto, precio_unitario)
                           VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            $sqlStock = "UPDATE producto SET stock_actual = stock_actual - :cantidad
                         WHERE id_producto = :id_producto";
            $stmtStock = $this->db->prepare($sqlStock);

            foreach ($lineas as $linea) {
                $stmtDetalle->execute([
                    ':id_pedido'       => $idPedido,
                    ':id_producto'     => $linea['id_producto'],
                    ':cantidad'        => $linea['cantidad'],
                    ':precio_unitario' => $linea['precio_unitario']
                ]);

                $stmtStock->execute([
                    ':cantidad'    => $linea['cantidad'],
                    ':id_producto' => $linea['id_producto']
                ]);
            }

            // Crear envío real si corresponde
            $idEnvio = null;
            if ($seleccionEnvio) {
                $idEnvio = $this->envioModel->crearEnvioFinal(
                    $idPedido,
                    $seleccionEnvio['id_domicilio'] ?? null
                );
            }

            $this->db->commit();

            // Limpiar solo el carrito desde sesión y la selección de envío
            unset($_SESSION['carrito']);
            $this->envioModel->limpiarSeleccionTemporal();

            return [
                'success'     => true,
                'id_pedido'   => $idPedido,
                'id_envio'    => $idEnvio,
                'monto_total' => $montoTotal
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =================================================================================
    // 2) OBTENER PEDIDO CON DETALLES (para Checkout / pago / seguimiento)
    // =================================================================================
    public function obtenerPedidoConDetalles(int $idPedido): ?array
    {
        $sqlPedido = "SELECT 
                p.*, e.nombre_estado AS estado_nombre
            FROM pedido p
            INNER JOIN estado_logico e ON e.id_estado_logico = p.id_estado_logico
            WHERE p.id_pedido = :idPedido LIMIT 1";

        $stmt = $this->db->prepare($sqlPedido);
        $stmt->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return null;
        }

        $sqlDetalles = "SELECT d.*, pr.nombre_producto, pr.imagen_producto
                        FROM detalle_pedido d
                        INNER JOIN producto pr ON pr.id_producto = d.id_producto
                        WHERE d.id_pedido = :idPedido";
        $stmtDet = $this->db->prepare($sqlDetalles);
        $stmtDet->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);
        $stmtDet->execute();

        $pedido['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    // =================================================================================
    // 3) PEDIDOS DE UN CLIENTE (panel del cliente)
    // =================================================================================
    /**
     * Obtiene todos los pedidos de un cliente con el nombre de estado lógico.
     */
    public function obtenerPedidosPorCliente(int $idCliente): array
    {
        $sql = "SELECT 
                    p.id_pedido,
                    p.fecha_pedido,
                    p.monto_total,
                    p.id_estado_logico,
                    el.nombre_estado AS estado_nombre
                FROM pedido p
                INNER JOIN estado_logico el ON el.id_estado_logico = p.id_estado_logico
                WHERE p.id_cliente = :idCliente
                ORDER BY p.fecha_pedido DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtiene pedidos del cliente con información de envío (estado, fechas).
     */
    public function obtenerPedidosConEstadoEnvio(int $idCliente): array
    {
        $sql = "SELECT 
                    p.id_pedido,
                    p.fecha_pedido,
                    p.monto_total,
                    el.nombre_estado AS estado_pedido,
                    e.estado AS estado_envio,
                    e.fecha_envio,
                    e.fecha_entrega
                FROM pedido p
                INNER JOIN estado_logico el ON el.id_estado_logico = p.id_estado_logico
                LEFT JOIN envio e ON e.id_pedido = p.id_pedido
                WHERE p.id_cliente = :idCliente
                ORDER BY p.fecha_pedido DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // =================================================================================
    // 4) OBTENER ENVÍO POR PEDIDO (para Checkout)
    // =================================================================================
    public function obtenerEnvioPorPedido(int $idPedido): ?array
    {
        try {
            $sql = "SELECT id_envio, id_pedido, id_domicilio
                    FROM envio
                    WHERE id_pedido = :id_pedido LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // =================================================================================
    // 5) ACTUALIZAR ESTADO LÓGICO DEL PEDIDO (por nombre)
    // =================================================================================
    /**
     * Cambia el estado lógico del pedido usando el nombre del estado,
     * por ejemplo: 'Enviado', 'Procesando', 'Pendiente', etc.
     */
    public function actualizarEstadoPedido(int $idPedido, string $nuevoEstado): bool
    {
        try {
            // Obtener el ID real del estado lógico
            $idEstado = $this->obtenerIdEstadoPorNombre($nuevoEstado);

            $sql = "UPDATE pedido 
                    SET id_estado_logico = :id_estado_logico
                    WHERE id_pedido = :idPedido";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_estado_logico', $idEstado, PDO::PARAM_INT);
            $stmt->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error actualizando estado del pedido: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el ID real del estado lógico según su nombre.
     * Ejemplo: obtenerIdEstadoPorNombre('Enviado').
     *
     * @param string $nombreEstado Nombre exacto del estado (Pendiente, Procesando, Enviado, etc.)
     * @return int
     * @throws Exception Si no existe el estado en la tabla.
     */
    private function obtenerIdEstadoPorNombre(string $nombreEstado): int
    {
        $sql = "SELECT id_estado_logico
                FROM estado_logico
                WHERE nombre_estado = :nombre
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombreEstado, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("El estado '{$nombreEstado}' no existe en estado_logico.");
        }

        return (int)$row['id_estado_logico'];
    }

    // =================================================================================
    // 6) HELPERS / UTILITARIOS INTERNOS
    // =================================================================================
    /**
     * Obtiene datos de un producto (precio y stock), con opción de FOR UPDATE
     * para usarse dentro de una transacción.
     */
    private function obtenerProductoParaPedido(int $idProducto, bool $forUpdate = false): ?array
    {
        $sql = "SELECT id_producto, nombre_producto, precio_venta, stock_actual
                FROM producto WHERE id_producto = :id_producto";

        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Devuelve el id_cliente real asociado a un id_usuario.
     */
    public function obtenerClienteIdPorUsuario(int $idUsuario): ?int
    {
        $sql = "SELECT c.id_cliente
                FROM usuario u
                INNER JOIN cliente c ON c.relacion_persona = u.relacion_persona
                WHERE u.id_usuario = :id_usuario LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_cliente'] : null;
    }
    public function obtenerPedidoCompleto(int $idPedido): ?array
{
$sql = "SELECT 
            p.id_pedido,
            p.fecha_pedido,
            p.monto_total,
            el.nombre_estado AS estado_pedido,
            c.id_cliente,
            per.nombre_persona,
            per.apellido_persona,
            per.fecha_nac_persona,
            gen.nombre_genero,
            u.nombre_usuario,

            -- 📧 Obtener SOLO el email (id_tipo_contacto = 1)
            (
                SELECT dc2.descripcion_contacto
                FROM detalle_contacto dc2
                WHERE dc2.id_detalle_contacto = per.id_detalle_contacto
                AND dc2.id_tipo_contacto = 1
                LIMIT 1
            ) AS email_usuario,

            d.calle_direccion,
            d.numero_direccion,
            d.piso_direccion,
            d.info_extra_direccion,
            b.nombre_barrio,
            l.nombre_localidad,
            pr.nombre_provincia,
            pa.nombre_pais

        FROM pedido p
        INNER JOIN estado_logico el ON el.id_estado_logico = p.id_estado_logico
        INNER JOIN cliente c ON c.id_cliente = p.id_cliente
        INNER JOIN persona per ON per.id_persona = c.relacion_persona
        LEFT JOIN genero gen ON gen.id_genero = per.id_genero
        LEFT JOIN usuario u ON u.relacion_persona = per.id_persona
        LEFT JOIN domicilio d ON d.id_domicilio = per.id_domicilio
        LEFT JOIN barrio b ON b.id_barrio = d.id_barrio
        LEFT JOIN localidad l ON l.id_localidad = b.id_localidad
        LEFT JOIN provincia pr ON pr.id_provincia = l.id_provincia
        LEFT JOIN pais pa ON pa.id_pais = pr.id_pais
        WHERE p.id_pedido = :idPedido
        LIMIT 1";


    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);
    $stmt->execute();

    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) return null;

    // Agregamos los productos
    $sqlDetalles = "SELECT 
                        d.id_producto,
                        pr.nombre_producto,
                        pr.imagen_producto,
                        d.cantidad_producto,
                        d.precio_unitario
                    FROM detalle_pedido d
                    INNER JOIN producto pr ON pr.id_producto = d.id_producto
                    WHERE d.id_pedido = :idPedido";
    $stmtDet = $this->db->prepare($sqlDetalles);
    $stmtDet->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);
    $stmtDet->execute();

    $pedido['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    return $pedido;
}

public function obtenerHistorialPedidosAdmin(int $idCliente): array
{
    $sql = "SELECT 
                p.id_pedido,
                p.fecha_pedido,
                p.monto_total,
                el.nombre_estado AS estado_pedido,
                pa.estado_pago,
                mp.nombre_metodo_pago
            FROM pedido p
            INNER JOIN estado_logico el ON p.id_estado_logico = el.id_estado_logico
            LEFT JOIN pago pa ON pa.id_pedido = p.id_pedido
            LEFT JOIN metodo_pago mp ON mp.id_metodo_pago = pa.id_metodo_pago
            WHERE p.id_cliente = :idCliente
            ORDER BY p.fecha_pedido DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}






}
