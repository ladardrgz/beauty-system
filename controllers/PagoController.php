<?php

require_once 'core/Sesion.php';
require_once 'models/Conexion.php';
require_once 'models/PedidoModel.php';
require_once 'models/EnvioModel.php';
require_once 'models/PagoModel.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use MercadoPago\MercadoPagoConfig;
use Dompdf\Dompdf;

class PagoController
{
    private $db;
    private $pedidoModel;
    private $envioModel;
    private $pagoModel;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->Conectar();

        $this->pedidoModel = new PedidoModel($this->db);
        $this->envioModel  = new EnvioModel($this->db);
        $this->pagoModel   = new PagoModel($this->db);
    }

    // ==========================================================
    // 1) Procesar pago desde Checkout API / Brick
    // ==========================================================
    public function procesarPagoAPI()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $raw  = file_get_contents("php://input");
            $data = json_decode($raw, true);

            file_put_contents(
                "logs_pago_api.txt",
                "\n[" . date("Y-m-d H:i:s") . "] RAW RECIBIDO: " . $raw . "\n",
                FILE_APPEND
            );

            if (!$data || !is_array($data)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se recibieron datos válidos del Brick.'
                ]);
                return;
            }

            $token        = $data['token'] ?? null;
            $methodId     = $data['payment_method_id'] ?? null;
            $issuerId     = $data['issuer_id'] ?? null;
            $installments = isset($data['installments']) ? (int) $data['installments'] : 1;
            $amount       = isset($data['transaction_amount'])
                ? (float) $data['transaction_amount']
                : (isset($data['amount']) ? (float) $data['amount'] : 0);
            $idPedido     = isset($data['id_pedido']) ? (int) $data['id_pedido'] : 0;

            if (empty($token) || $idPedido <= 0 || $amount <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan datos obligatorios del pago.'
                ]);
                return;
            }

            MercadoPagoConfig::setAccessToken($_ENV['MP_ACCESS_TOKEN']);
            $client = new \MercadoPago\Client\Payment\PaymentClient();

            $paymentData = [
                "transaction_amount" => $amount,
                "token"              => $token,
                "description"        => "Pago del pedido #{$idPedido}",
                "installments"       => $installments,
                "payer" => [
                    "email" => $_SESSION['usuario']['email_usuario'] ?? "cliente@mizzastore.com"
                ]
            ];

            if (!empty($methodId)) {
                $paymentData["payment_method_id"] = $methodId;
            }

            if (!empty($issuerId)) {
                $paymentData["issuer_id"] = $issuerId;
            }

            $payment = $client->create($paymentData);

            $estadoMP     = $payment->status ?? 'unknown';
            $statusDetail = $payment->status_detail ?? null;
            $paymentId    = $payment->id ?? null;

            if (!$paymentId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mercado Pago no devolvió un ID de pago válido.'
                ]);
                return;
            }

            // Registrar el pago inicial y asociar el ID de MP
            $this->pagoModel->crearPagoPendiente($idPedido, $amount);
            $this->pagoModel->actualizarPaymentId($idPedido, (string) $paymentId);
            $this->pagoModel->actualizarEstadoPago((string) $paymentId, (string) $estadoMP);

            // Estado lógico del pedido según estado real de MP
            if ($estadoMP === "approved") {
                $this->actualizarEstadoPedido($idPedido, 12); // Confirmado
            } elseif (in_array($estadoMP, ["rejected", "cancelled", "charged_back"])) {
                $this->actualizarEstadoPedido($idPedido, 11); // Fallido / cancelado
            }

            echo json_encode([
                'success'    => true,
                'estado'     => $estadoMP,      // estado real para el frontend
                'detalle'    => $statusDetail,
                'payment_id' => $paymentId,
                'id_pedido'  => $idPedido
            ]);
            return;

        } catch (\Exception $e) {
            file_put_contents(
                "logs_pago_api.txt",
                "\n[" . date("Y-m-d H:i:s") . "] ERROR: " . $e->getMessage() . "\n",
                FILE_APPEND
            );

            echo json_encode([
                'success' => false,
                'message' => 'Error interno al procesar el pago.'
            ]);
            return;
        }
    }

    // ==========================================================
    // 2) Webhook de Mercado Pago
    // ==========================================================
    public function webhookMP()
    {
        $raw  = file_get_contents("php://input");
        $data = json_decode($raw, true);

        file_put_contents(
            "logs_mp_webhook.txt",
            "[" . date("Y-m-d H:i:s") . "] → " . $raw . "\n",
            FILE_APPEND
        );

        $paymentId = $data['data']['id'] ?? ($_GET['id'] ?? null);
        $type      = $data['type'] ?? ($_GET['type'] ?? null);

        if (!$paymentId || $type !== "payment") {
            http_response_code(200);
            echo "IGNORED";
            return;
        }

        try {
            MercadoPagoConfig::setAccessToken($_ENV['MP_ACCESS_TOKEN']);

            $client  = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($paymentId);

            $estadoMP = $payment->status ?? 'unknown';

            // Actualizar estado interno del pago
            $this->pagoModel->actualizarEstadoPago((string) $paymentId, (string) $estadoMP);

            // Buscar pedido asociado al payment_mp_id
            $stmt = $this->db->prepare("
                SELECT id_pedido
                FROM pago
                WHERE payment_mp_id = :pid
                ORDER BY id_pago DESC
                LIMIT 1
            ");
            $stmt->bindParam(':pid', $paymentId, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $idPedido = (int) $row['id_pedido'];

                if ($estadoMP === "approved") {
                    $this->actualizarEstadoPedido($idPedido, 12);
                } elseif (in_array($estadoMP, ["rejected", "cancelled", "charged_back"])) {
                    $this->actualizarEstadoPedido($idPedido, 11);
                }
            }

            http_response_code(200);
            echo "OK";
            return;

        } catch (\Exception $e) {
            error_log("WEBHOOK ERROR → " . $e->getMessage());
            http_response_code(500);
            echo "ERROR";
            return;
        }
    }

    // ==========================================================
    // 3) Actualizar estado lógico del pedido
    // ==========================================================
    private function actualizarEstadoPedido(int $idPedido, int $estado): void
    {
        $sql = "UPDATE pedido
                SET id_estado_logico = :estado
                WHERE id_pedido = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
        $stmt->bindParam(':id', $idPedido, PDO::PARAM_INT);
        $stmt->execute();
    }

    // ==========================================================
    // 4) Pantalla de confirmación + PDF + email
    // ==========================================================
    public function confirmacionPago()
    {
        if (empty($_GET['id_pedido'])) {
            echo "<div class='alert alert-danger text-center mt-5'>Pedido no válido.</div>";
            return;
        }

        $idPedido = (int) $_GET['id_pedido'];

        $pedido = $this->pedidoModel->obtenerPedidoCompleto($idPedido);
        $pago   = $this->pagoModel->obtenerPagoCompletoPorPedido($idPedido);

        if (!$pedido || !$pago) {
            echo "<div class='alert alert-danger text-center mt-5'>No se pudo cargar la información del pago.</div>";
            return;
        }

        // OJO: según el modelo, approved se guarda como completado
        if ($pago['estado_pago'] === 'completado') {
            $rutaPDF = "views/pago/pdf_generados/comprobante_{$pedido['id_pedido']}.pdf";

            if (!file_exists($rutaPDF)) {
                $this->generarComprobantePDF($pedido, $pago);
            }

            // Por ahora queda así. Más adelante conviene guardar un flag
            // en la BD para no reenviar el mail cada vez que entren acá.
            $this->enviarMailConfirmacion($pedido, $pago);
        }

        $data = [
            'pedido' => $pedido,
            'pago'   => $pago
        ];

        $vista = 'views/pago/confirmacion_pago.php';
        require_once 'views/layouts/main.php';
    }

    // ==========================================================
    // 5) Generar comprobante PDF
    // ==========================================================
    private function generarComprobantePDF(array $pedido, array $pago): void
    {
        $dompdf = new Dompdf();

        ob_start();
        include 'views/pago/comprobante_pdf.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $carpeta = "views/pago/pdf_generados/";
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $filePath = $carpeta . "comprobante_{$pedido['id_pedido']}.pdf";
        file_put_contents($filePath, $dompdf->output());
    }

    // ==========================================================
    // 6) Enviar mail de confirmación
    // ==========================================================
    private function enviarMailConfirmacion(array $pedido, array $pago): bool
    {
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($pedido['email_usuario']);

            $mail->isHTML(true);
            $mail->Subject = "Confirmación de pago - Pedido #{$pedido['id_pedido']}";

            $productosHtml = '';
            foreach ($pedido['detalles'] as $item) {
                $subtotal = number_format($item['precio_unitario'] * $item['cantidad_producto'], 2);

                $productosHtml .= "<tr>
                    <td>{$item['nombre_producto']}</td>
                    <td>{$item['cantidad_producto']}</td>
                    <td>$ {$item['precio_unitario']}</td>
                    <td>$ {$subtotal}</td>
                </tr>";
            }

            $mail->Body = "
                <h2>Gracias por tu compra</h2>
                <p>Hola <strong>{$pedido['nombre_persona']} {$pedido['apellido_persona']}</strong>, tu pago fue aprobado correctamente.</p>
                <p>Pedido N° {$pedido['id_pedido']} | Fecha: {$pedido['fecha_pedido']}</p>
                <table border='1' cellpadding='5' cellspacing='0' width='100%'>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$productosHtml}
                    </tbody>
                </table>
            ";

            $rutaPDF = "views/pago/pdf_generados/comprobante_{$pedido['id_pedido']}.pdf";
            if (file_exists($rutaPDF)) {
                $mail->addAttachment($rutaPDF);
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Error enviando mail: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================================
    // 7) Descargar comprobante PDF
    // ==========================================================
public function descargarComprobante()
{
    if (empty($_GET['id_pedido'])) {
        echo "<div class='alert alert-danger text-center mt-5'>Pedido no válido.</div>";
        return;
    }

    $idPedido = (int) $_GET['id_pedido'];

    $pedido = $this->pedidoModel->obtenerPedidoCompleto($idPedido);
    $pago   = $this->pagoModel->obtenerPagoCompletoPorPedido($idPedido);

    if (!$pedido || !$pago) {
        echo "<div class='alert alert-danger text-center mt-5'>
                No se pudo cargar la información del comprobante.
              </div>";
        return;
    }

    // Solo permitir comprobante si el pago está completado
    if (($pago['estado_pago'] ?? 'pendiente') !== 'completado') {
        echo "<div class='alert alert-warning text-center mt-5'>
                El comprobante solo está disponible cuando el pago está completado.
              </div>";
        return;
    }

    $rutaPDF = "views/pago/pdf_generados/comprobante_{$idPedido}.pdf";

    // Si no existe, lo generamos
    if (!file_exists($rutaPDF)) {
        $this->generarComprobantePDF($pedido, $pago);
    }

    // Verificación final
    if (!file_exists($rutaPDF)) {
        echo "<div class='alert alert-warning text-center mt-5'>
                No se pudo generar el comprobante del pedido #{$idPedido}.
              </div>";
        return;
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Comprobante_Pedido_' . $idPedido . '.pdf"');
    readfile($rutaPDF);
    exit;
}
}