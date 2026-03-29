<head>
    <style>
        :root {
            --color-pendiente: #f0ad4e;
            --color-procesando: #5bc0de;
            --color-en-camino: #007bff;
            --color-entregado: #28a745;
            --color-cancelado: #dc3545;
            --gris-texto: #4a4a4a;
            --gris-claro: #e9ecef;
            --blanco: #ffffff;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            color: var(--gris-texto);
        }

        .card {
            border: none;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            border-radius: 14px;
        }

        .card-header {
            background: #ffffff !important;
            border-bottom: 2px solid #f1f1f1;
            font-weight: 600;
            font-size: 16px;
        }

        .tracking-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 25px;
            padding: 25px 10px;
            align-items: center;
        }

        .tracking-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 8%;
            right: 8%;
            height: 4px;
            background-color: var(--gris-claro);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: var(--gris-claro);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 6px;
            transition: transform 0.3s ease-in-out;
        }

        .step.completed {
            background-color: var(--color-entregado);
            color: white;
        }

        .step[class*="active"] {
            animation: pulse 1.2s infinite ease-in-out;
            transform: scale(1.05);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.12);
            }

            100% {
                transform: scale(1);
            }
        }

        .step.active-pendiente {
            background-color: var(--color-pendiente);
            color: white;
        }

        .step.active-procesando {
            background-color: var(--color-procesando);
            color: white;
        }

        .step.active-en_camino {
            background-color: var(--color-en-camino);
            color: white;
        }

        .step.active-entregado {
            background-color: var(--color-entregado);
            color: white;
        }

        .step-label {
            text-align: center;
            font-size: 13px;
            margin-top: 8px;
            text-transform: capitalize;
        }

        .badge-estado {
            font-size: 13px;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 600;
            color: white;
        }

        .badge-pendiente {
            background-color: var(--color-pendiente);
        }

        .badge-procesando {
            background-color: var(--color-procesando);
        }

        .badge-en_camino {
            background-color: var(--color-en-camino);
        }

        .badge-entregado {
            background-color: var(--color-entregado);
        }

        .badge-cancelado {
            background-color: var(--color-cancelado);
        }

        .toast-custom {
            background: #ffcc00;
            color: #222;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 6px solid #ff8c00;
            animation: slideUp 0.8s ease-in-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(60px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .btn-whatsapp {
            background-color: #25d366;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .icon-wsp {
            width: 18px;
            height: 18px;
        }
    </style>
</head>

<?php
$pedidos = $data['pedidos'] ?? [];
$pedidosEnvio = $data['pedidos_envio'] ?? [];
$usuario = Sesion::obtenerUsuario();
$usuarioNombre = $usuario['nombre_usuario'] ?? 'Cliente';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8"> <!-- Centrado y con ancho controlado -->
            <div class="card mb-4">
                <div class="card-header">Tus pedidos recientes</div>
                <div class="card-body">

                    <?php if (empty($pedidos)) : ?>
                        <p class="text-center text-muted">Aún no tienes pedidos registrados.</p>
                    <?php else : ?>
                        <?php foreach ($pedidos as $p) : ?>
                            <?php
                            $estadoEnvio = '-';
                            $repartidorNombre = '-';

                            if (!empty($pedidosEnvio)) {
                                foreach ($pedidosEnvio as $envio) {
                                    if ($envio['id_pedido'] == $p['id_pedido']) {
                                        $estadoEnvio = $envio['estado_envio'] ?? '-';
                                        $repartidorNombre = !empty($envio['nombre_repartidor'])
                                            ? $envio['nombre_repartidor'] . ' ' . $envio['apellido_repartidor']
                                            : '-';
                                        break;
                                    }
                                }
                            }

                            $estados = ['pendiente', 'procesando', 'en camino', 'entregado'];
                            $estadoActual = strtolower($estadoEnvio !== '-' ? $estadoEnvio : $p['estado_nombre']);
                            $estadoIndex = array_search($estadoActual, $estados);
                            ?>

                            <div class="border rounded p-3 mb-3 shadow-sm bg-white">

                                <strong>Pedido #<?= $p['id_pedido'] ?></strong><br>
                                <small>Fecha: <?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></small><br>
                                <small>Monto total: $<?= number_format($p['monto_total'], 2) ?></small><br>

                                <span class="badge-estado badge-<?= strtolower(str_replace(' ', '_', $estadoActual)) ?>">
                                    <?= ucfirst($estadoActual) ?>
                                </span>

                                <?php if ($estadoActual !== 'pendiente' && $repartidorNombre !== '-') : ?>
                                    <br><small><strong>Repartidor:</strong> <?= $repartidorNombre ?></small>
                                <?php endif; ?>

                                <div class="tracking-container">
                                    <?php foreach ($estados as $i => $nombreEstado): ?>
                                        <?php
                                        $stepClass = '';
                                        if ($i < $estadoIndex) {
                                            $stepClass = 'completed';
                                        } elseif ($i === $estadoIndex) {
                                            $stepClass = 'active-' . str_replace(' ', '_', $nombreEstado);
                                        }
                                        ?>
                                        <div class="text-center flex-fill">
                                            <div class="step <?= $stepClass; ?>"></div>
                                            <div class="step-label"><?= ucfirst($nombreEstado) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php
                                $horaPedido = strtotime($p['fecha_pedido']);
                                $horasPasadas = (time() - $horaPedido) / 3600;
                                if ($horasPasadas > 4 && $estadoActual !== 'entregado'):
                                    $whatsappMsg = rawurlencode("Hola, han pasado más de 4 horas y aún no recibí mi pedido #{$p['id_pedido']}.");
                                ?>
                                    <a href="https://wa.me/3704224812?text=<?= $whatsappMsg ?>"
                                        target="_blank"
                                        class="btn btn-whatsapp mt-3">
                                        <img src="assets/images/wsp.png" alt="WhatsApp" class="icon-wsp">
                                        ¿Pasaron más de 4 horas sin recibir su pedido?
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($pedidosEnvio)) : ?>
            <?php foreach ($pedidosEnvio as $envio): ?>
                <?php if ($envio['estado_envio'] === 'en camino'): ?>
                    mostrarToast("¡Tu pedido #<?= $envio['id_pedido'] ?> está en camino!");
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    });

    function mostrarToast(mensaje) {
        const toastHTML = `
        <div class="toast toast-custom show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${mensaje}</div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

        const container = document.createElement('div');
        container.className = "toast-container position-fixed bottom-0 end-0 p-3";
        container.innerHTML = toastHTML;
        document.body.appendChild(container);

        setTimeout(() => {
            const toastEl = container.querySelector('.toast');
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }, 100);
    }
</script>