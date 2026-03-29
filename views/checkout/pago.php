<?php
if (!isset($data) || empty($data)) {
    echo "<div class='alert alert-danger text-center mt-5'>No se pudo cargar el pedido.</div>";
    return;
}

$pedido    = $data['pedido']    ?? null;
$envio     = $data['envio']     ?? null;
$domicilio = $data['domicilio'] ?? null;

if (!$pedido) {
    echo "<div class='alert alert-danger text-center mt-5'>Pedido no encontrado.</div>";
    return;
}
?>

<div class="container py-5">
    <h2 class="text-center mb-4" style="color:#880e4f;">Resumen del Pedido</h2>

    <!-- Número del pedido -->
    <div class="card shadow-sm p-3 mb-4">
        <h5 class="mb-0">Número de pedido: <strong>#<?= $pedido['id_pedido'] ?></strong></h5>
        <small class="text-muted">Fecha: <?= $pedido['fecha_pedido'] ?></small>
    </div>

    <!-- Datos del cliente -->
    <div class="card shadow-sm p-3 mb-4">
        <h5 class="fw-bold" style="color:#880e4f;">Tus datos</h5>
        <p class="mb-1"><strong>Nombre:</strong> <?= $_SESSION['usuario']['nombre_usuario'] ?></p>
    </div>

    <!-- Método de entrega -->
    <div class="card shadow-sm p-3 mb-4">
        <h5 class="fw-bold" style="color:#880e4f;">Método de entrega</h5>

        <?php if ($envio && empty($envio['id_domicilio'])): ?>
            <p><strong>Retiro en local:</strong> Moreno 806, Formosa</p>
        <?php else: ?>
            <p><strong>Enviar a domicilio:</strong></p>
            <p class="mb-0"><?= $domicilio['texto_formateado'] ?? '' ?></p>
        <?php endif; ?>
    </div>

    <!-- Detalles del pedido -->
    <div class="card shadow-sm p-3 mb-4">
        <h5 class="fw-bold" style="color:#880e4f;">Productos</h5>

        <ul class="list-group">
            <?php foreach ($pedido['detalles'] as $item): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span><?= $item['nombre_producto'] ?> x <?= $item['cantidad_producto'] ?></span>
                    <strong>$<?= number_format($item['precio_unitario'] * $item['cantidad_producto'], 2) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="mt-3 text-end">
            <h4>Total:
                <strong class="text-success">
                    $<?= number_format($pedido['monto_total'], 2) ?>
                </strong>
            </h4>
        </div>
    </div>

<!-- ========================================================= -->
<!-- CARD PAYMENT BRICK (Checkout API) -->
<!-- ========================================================= -->
<div class="text-center mt-4">

    <!-- Contenedor del Brick -->
    <div id="cardPaymentBrick_container" class="my-4"></div>

    <!-- SDK Mercado Pago -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    // ==========================================================
    // Inicializar Mercado Pago con PUBLIC KEY desde backend
    // ==========================================================
    const mp = new MercadoPago(
        "<?= $_ENV['MP_PUBLIC_KEY']; ?>",
        { locale: "es-AR" }
    );

    const amount = Number(<?= json_encode((float)$pedido['monto_total']); ?>);
    const idPedido = Number(<?= json_encode((int)$pedido['id_pedido']); ?>);

    // ==========================================================
    // Crear Card Payment Brick
    // ==========================================================
    mp.bricks().create("cardPayment", "cardPaymentBrick_container", {
        initialization: {
            amount: amount
        },

        callbacks: {
            onReady: () => {
                console.log("Card Payment Brick listo.");
            },

            onError: (error) => {
                console.error("Error en Brick:", error);

                Swal.fire(
                    "Error",
                    "Ocurrió un problema al cargar el formulario de pago.",
                    "error"
                );
            },

            // ======================================================
            // ENVÍO REAL DE DATOS DEL BRICK AL BACKEND
            // ======================================================
            onSubmit: async (formData) => {
                console.log("formData recibido del Brick:", formData);
                console.log("Pedido:", idPedido);

                try {
                    const payload = {
                        token: formData.token ?? null,
                        payment_method_id: formData.payment_method_id ?? formData.paymentMethodId ?? null,
                        issuer_id: formData.issuer_id ?? formData.issuerId ?? null,
                        installments: Number(formData.installments ?? 1),
                        transaction_amount: Number(
                            formData.transaction_amount ??
                            formData.amount ??
                            amount
                        ),
                        id_pedido: idPedido
                    };

                    const response = await fetch(
                        "index.php?controller=Pago&action=procesarPagoAPI",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(payload)
                        }
                    );

                    const result = await response.json();
                    console.log("Respuesta del backend:", result);

                    if (!result.success) {
                        Swal.fire(
                            "Error",
                            result.message || "No se pudo procesar el pago.",
                            "error"
                        );
                        return;
                    }

                    // ==================================================
                    // Manejo correcto según el estado devuelto por MP
                    // ==================================================
                    if (result.estado === "approved") {
                        Swal.fire({
                            icon: "success",
                            title: "Pago aprobado",
                            text: "Tu pago fue aprobado correctamente.",
                            timer: 2200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                "index.php?controller=Pago&action=confirmacionPago&id_pedido=" + idPedido;
                        });
                        return;
                    }

                    if (result.estado === "pending" || result.estado === "in_process") {
                        Swal.fire({
                            icon: "info",
                            title: "Pago en proceso",
                            text: "Tu pago fue enviado y está pendiente de confirmación. Te avisaremos cuando se acredite."
                        }).then(() => {
                            window.location.href =
                                "index.php?controller=Pago&action=confirmacionPago&id_pedido=" + idPedido;
                        });
                        return;
                    }

                    if (
                        result.estado === "rejected" ||
                        result.estado === "cancelled" ||
                        result.estado === "charged_back"
                    ) {
                        Swal.fire({
                            icon: "error",
                            title: "Pago no aprobado",
                            text: "El pago fue rechazado o cancelado. Probá con otro medio de pago."
                        });
                        return;
                    }

                    // Estado inesperado
                    Swal.fire({
                        icon: "warning",
                        title: "Estado desconocido",
                        text: "El pago fue procesado, pero no pudimos determinar su estado final."
                    });

                } catch (err) {
                    console.error("Error al enviar pago:", err);

                    Swal.fire(
                        "Error",
                        "No se pudo conectar con el servidor para procesar el pago.",
                        "error"
                    );
                }
            }
        }
    });
</script>