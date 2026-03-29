<style>
.buscador-clientes {
    margin-bottom: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.buscador-clientes input,
.buscador-clientes select {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    min-width: 160px;
    transition: all 0.2s ease-in-out;
    background-color: #fff;
}

.buscador-clientes input {
    flex: 1;
    min-width: 260px;
}

.buscador-clientes input:focus,
.buscador-clientes select:focus {
    border-color: #a64b79;
    box-shadow: 0 0 4px rgba(166, 75, 121, 0.5);
    outline: none;
}

/* 📋 Tabla */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background-color: white;
    font-size: 15px;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

/* Encabezado estilo Gestión de Clientes */
table th {
    background: #7a1c4b;
    color: white;
    padding: 12px;
    font-weight: 600;
    text-align: left;
    border-bottom: 2px solid #5e163a;
}

/* Celdas */
table td {
    padding: 12px 10px;
    border-bottom: 1px solid #e5e2e2;
    color: #333;
}

/* Hover suave */
tr:hover td {
    background-color: #faf4f7;
}

/* 🔘 Botones de acción */
.btn-accion {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    margin: 0 4px;
    transition: transform 0.2s ease;
}

.btn-accion:hover {
    transform: scale(1.1);
}

.icono-tabla {
    width: 22px;
    height: 22px;
}

/* 📎 Paginador */
.paginador {
    margin-top: 15px;
    text-align: center;
}

.paginador button {
    padding: 6px 12px;
    margin: 0 4px;
    border: 1px solid #d3d3d3;
    background: #f8f8f8;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.paginador button:hover {
    background: #e4d0da;
}

.paginador button.activo {
    background: #7a1c4b;
    color: white;
    border-color: #7a1c4b;
}

</style>

<div class="buscador-clientes">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <input type="text" id="buscarPedido" placeholder="Buscar por número de pedido...">

        <select id="filtroEstadoPedido">
            <option value="">Estado pedido</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Procesando">Procesando</option>
            <option value="Enviado">Enviado</option>
            <option value="Entregado">Entregado</option>
            <option value="Cancelado">Cancelado</option>
        </select>

        <select id="filtroEstadoPago">
            <option value="">Estado pago</option>
            <option value="Pagado">Pagado</option>
            <option value="Pendiente">Pendiente</option>
        </select>

        <select id="registrosPorPagina">
            <option value="5" selected>5</option>
            <option value="10">10</option>
            <option value="20">20</option>
        </select>
    </div>
</div>

<!-- Tabla de historial -->
<table id="tablaHistorial">
    <thead>
        <tr>
            <th>Pedido #</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado del pedido</th>
            <th>Estado del pago</th>
            <th style="text-align:center;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dinámico JS -->
    </tbody>
</table>

<!-- paginación -->
<div id="paginadorHistorial" class="paginador"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let paginaActual = 1;

    const buscarPedido       = document.querySelector('#buscarPedido');
    const filtroEstadoPedido = document.querySelector('#filtroEstadoPedido');
    const filtroEstadoPago   = document.querySelector('#filtroEstadoPago');
    const registrosPorPagina = document.querySelector('#registrosPorPagina');
    const tablaBody          = document.querySelector('#tablaHistorial tbody');
    const paginador          = document.querySelector('#paginadorHistorial');

    // ID de cliente desde la URL (?id=...)
    const idCliente = new URLSearchParams(window.location.search).get('id');

    if (!idCliente) {
        Swal.fire('Error', 'ID de cliente no proporcionado', 'error');
        return;
    }

    // Carga inicial
    cargarHistorial();

    // Eventos de filtros / paginación
    buscarPedido.addEventListener('input', function () { cambiarPagina(1); });
    filtroEstadoPedido.addEventListener('change', function () { cambiarPagina(1); });
    filtroEstadoPago.addEventListener('change', function () { cambiarPagina(1); });
    registrosPorPagina.addEventListener('change', function () { cambiarPagina(1); });

    function cambiarPagina(nuevaPagina) {
        paginaActual = nuevaPagina;
        cargarHistorial();
    }

    function cargarHistorial() {
        // Mensaje de carga
        tablaBody.innerHTML = ''
            + '<tr>'
            + '  <td colspan="6" style="text-align:center;">Cargando...</td>'
            + '</tr>';

        const params = new URLSearchParams();
        params.append('pagina', paginaActual);
        params.append('registrosPorPagina', registrosPorPagina.value);
        params.append('idCliente', idCliente);

        // Solo agregamos filtros si tienen valor
        if (buscarPedido.value.trim() !== '') {
            params.append('buscar', buscarPedido.value.trim());
        }
        if (filtroEstadoPedido.value !== '') {
            params.append('estado_pedido', filtroEstadoPedido.value);
        }
        if (filtroEstadoPago.value !== '') {
            params.append('estado_pago', filtroEstadoPago.value);
        }

        fetch('index.php?controller=Cliente&action=obtenerHistorialComprasAdmin&' + params.toString())
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    renderizarTabla(data.data);
                    renderizarPaginador(data.totalPaginas);
                } else {
                    tablaBody.innerHTML = ''
                        + '<tr>'
                        + '  <td colspan="6" style="text-align:center;">No hay registros</td>'
                        + '</tr>';
                    paginador.innerHTML = '';
                }
            })
            .catch(function (err) {
                console.error('Error cargando historial:', err);
                Swal.fire('Error', 'Error al cargar datos del historial', 'error');
            });
    }

    function renderizarTabla(historial) {
        tablaBody.innerHTML = '';

        historial.forEach(function (pedido) {
            const fila = document.createElement('tr');

            fila.innerHTML = ''
                + '<td>#' + pedido.id_pedido + '</td>'
                + '<td>' + (pedido.fecha_pedido || '') + '</td>'
                + '<td>$' + (pedido.monto_total || '0') + '</td>'
                + '<td>' + (pedido.estado_pedido || '-') + '</td>'
                + '<td>' + (pedido.estado_pago ? pedido.estado_pago : '-') + '</td>'
                + '<td style="text-align:center;">'
                + '  <button class="btn-accion" onclick="exportarPDF(' + pedido.id_pedido + ')" title="Descargar comprobante PDF">'
                + '      <img src="assets/images/icons/dowload-pdf.png" class="icono-tabla">'
                + '  </button>'
                + '</td>';

            tablaBody.appendChild(fila);
        });
    }

    function renderizarPaginador(totalPaginas) {
        paginador.innerHTML = '';

        if (!totalPaginas || totalPaginas <= 1) {
            return;
        }

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;

            if (i === paginaActual) {
                btn.classList.add('activo');
            }

            btn.addEventListener('click', function () {
                cambiarPagina(i);
            });

            paginador.appendChild(btn);
        }
    }

    // Función global para descargar PDF (usa PagoController::descargarComprobante)
    window.exportarPDF = function (idPedido) {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Esto puede tardar unos segundos.',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        window.location.href =
            'index.php?controller=Pago&action=descargarComprobante&id_pedido=' + encodeURIComponent(idPedido);

        setTimeout(function () {
            Swal.close();
        }, 2000);
    };
});
</script>
