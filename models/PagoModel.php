<?php

class PagoModel
{
    private $db;

    public function __construct($db)
    {
        // Recibe la conexión PDO desde el controlador
        $this->db = $db;
    }

    // ==========================================================
    // CREAR REGISTRO INICIAL DE PAGO
    // ==========================================================
    public function crearPagoPendiente(int $idPedido, float $monto): int
    {
        try {
            $sql = "INSERT INTO pago (
                        id_pedido,
                        id_metodo_pago,
                        estado_pago,
                        monto_pago
                    ) VALUES (
                        :id_pedido,
                        3,
                        'pendiente',
                        :monto_pago
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->bindParam(':monto_pago', $monto);
            $stmt->execute();

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error crearPagoPendiente: " . $e->getMessage());
            return 0;
        }
    }

    // ==========================================================
    // ASOCIAR EL PAYMENT ID DEVUELTO POR MERCADO PAGO
    // ==========================================================
    public function actualizarPaymentId(int $idPedido, string $paymentId): bool
    {
        try {
            $sql = "UPDATE pago
                    SET payment_mp_id = :payment_mp_id
                    WHERE id_pedido = :id_pedido
                    ORDER BY id_pago DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':payment_mp_id', $paymentId, PDO::PARAM_STR);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizarPaymentId: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================================
    // ACTUALIZAR ESTADO INTERNO SEGÚN ESTADO DE MERCADO PAGO
    // approved     -> completado
    // pending      -> pendiente
    // in_process   -> pendiente
    // rejected     -> fallido
    // cancelled    -> fallido
    // charged_back -> fallido
    // ==========================================================
    public function actualizarEstadoPago(string $paymentId, string $estadoMP): bool
    {
        $estadoBD = $this->mapearEstadoMercadoPago($estadoMP);

        try {
            $sql = "UPDATE pago
                    SET estado_pago = :estado
                    WHERE payment_mp_id = :payment_id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estadoBD, PDO::PARAM_STR);
            $stmt->bindParam(':payment_id', $paymentId, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizarEstadoPago: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================================
    // MAPEAR ESTADO DE MP A ESTADO INTERNO DEL SISTEMA
    // ==========================================================
    private function mapearEstadoMercadoPago(string $estadoMP): string
    {
        switch ($estadoMP) {
            case 'approved':
                return 'completado';

            case 'pending':
            case 'in_process':
                return 'pendiente';

            case 'rejected':
            case 'cancelled':
            case 'charged_back':
                return 'fallido';

            default:
                return 'pendiente';
        }
    }

    // ==========================================================
    // OBTENER EL ÚLTIMO PAGO BÁSICO DE UN PEDIDO
    // ==========================================================
    public function obtenerPagoPorPedido(int $idPedido): ?array
    {
        try {
            $sql = "SELECT *
                    FROM pago
                    WHERE id_pedido = :id_pedido
                    ORDER BY id_pago DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error obtenerPagoPorPedido: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================================
    // OBTENER EL PAGO COMPLETO CON SU MÉTODO DE PAGO
    // ==========================================================
    public function obtenerPagoCompletoPorPedido(int $idPedido): ?array
    {
        try {
            $sql = "SELECT 
                        p.id_pago,
                        p.payment_mp_id,
                        p.id_pedido,
                        p.estado_pago,
                        p.monto_pago,
                        p.id_metodo_pago,
                        mp.nombre_metodo_pago
                    FROM pago p
                    INNER JOIN metodo_pago mp 
                        ON mp.id_metodo_pago = p.id_metodo_pago
                    WHERE p.id_pedido = :id_pedido
                    ORDER BY p.id_pago DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error obtenerPagoCompletoPorPedido: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================================
    // VERIFICAR SI EL PEDIDO YA TIENE UN PAGO REGISTRADO
    // Útil si después querés evitar duplicados innecesarios
    // ==========================================================
    public function existePagoParaPedido(int $idPedido): bool
    {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM pago
                    WHERE id_pedido = :id_pedido";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error existePagoParaPedido: " . $e->getMessage());
            return false;
        }
    }
}