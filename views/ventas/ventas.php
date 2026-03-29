<style>
/* Paleta institucional */
:root {
    --bordo: #7a1c4b;
    --rosa-medio: #d94b8c;
    --rosa-claro: #f9e2ec;
    --texto-oscuro: #2b1a1f;
    --borde-claro: #f0c8d8;
    --sombra: rgba(0, 0, 0, 0.08);
}

/* CONTENEDOR GENERAL */
.contenedor-productos {
    width: 92%;
    max-width: 1250px;
    margin: 20px auto;
    padding: 30px 25px;
    background: #fff;
    color: var(--texto-oscuro);
    font-family: 'Segoe UI', Tahoma, sans-serif;
    border-radius: 14px;
    box-shadow: 0 4px 12px var(--sombra);
}

/* ENCABEZADO */
.contenedor-productos h2 {
    font-size: 1.7rem;
    margin-bottom: 5px;
    color: var(--bordo);
    font-weight: 600;
    letter-spacing: 0.4px;
}

.subtitulo {
    font-size: 0.95rem;
    color: #666;
    margin-bottom: 22px;
}

/* BUSCADOR */
.buscador {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.buscador input,
.buscador select {
    padding: 8px 10px;
    border: 1px solid var(--rosa-medio);
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    color: var(--texto-oscuro);
    background-color: #fff;
    transition: all 0.2s linear;
}

.buscador input:focus,
.buscador select:focus {
    border-color: var(--bordo);
    box-shadow: 0 0 4px rgba(122, 28, 75, 0.3);
}

/* BOTONES PRINCIPALES */
.btn-primario {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, var(--rosa-medio), var(--bordo));
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 9px 16px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 6px rgba(122, 28, 75, 0.25);
    transition: all 0.25s ease;
}

.btn-primario:hover {
    background: linear-gradient(135deg, var(--bordo), var(--rosa-medio));
    box-shadow: 0 4px 10px rgba(122, 28, 75, 0.35);
}

.icono-btn {
    width: 18px;
    height: 18px;
    filter: brightness(0) invert(1);
}

/* TABLA PRINCIPAL */
.tabla-contenedor {
    width: 100%;
    max-height: 65vh;
    overflow: auto;
    border: 1px solid var(--borde-claro);
    border-radius: 10px;
}

#tablaPedidos {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

#tablaPedidos thead {
    background: var(--bordo);
    color: #fff;
}

#tablaPedidos th,
#tablaPedidos td {
    padding: 10px;
    font-size: 14px;
}

#tablaPedidos tbody tr:hover {
    background-color: var(--rosa-claro);
}

#tablaPedidos td {
    border-bottom: 1px solid var(--borde-claro);
}

/* BOTONES DE ACCIÓN */
.btn-accion {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 2px;
    transition: transform 0.15s, opacity 0.15s;
}

.btn-accion:hover {
    transform: scale(1.1);
    opacity: 0.8;
}

.icono-tabla {
    width: 19px;
    height: 19px;
}

/* PAGINADOR */
.paginador {
    margin-top: 18px;
    text-align: center;
}

.paginador button {
    margin: 2px;
    padding: 6px 12px;
    border: 1px solid var(--rosa-medio);
    background: #fff;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    color: var(--bordo);
    transition: 0.2s;
}

.paginador button.activo,
.paginador button:hover {
    background-color: var(--bordo);
    color: #fff;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    width: 520px;
    border-top: 6px solid var(--bordo);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
    max-height: 85vh;
    overflow-y: auto;
}

.modal-content h3 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 1.2rem;
    color: var(--bordo);
}

/* ACCIONES DEL MODAL */
.acciones-modal {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 18px;
}

.btn-secundario {
    background: var(--rosa-claro);
    border: 1px solid var(--rosa-medio);
    color: var(--bordo);
    border-radius: 5px;
    padding: 7px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-secundario:hover {
    background: var(--rosa-medio);
    color: #fff;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .contenedor-productos {
        padding: 18px;
    }
    .modal-content {
        width: 92%;
    }
    #tablaPedidos th,
    #tablaPedidos td {
        font-size: 13px;
    }
}
</style><div class="contenedor-productos">
    <h2>Gestión de pedidos</h2>
    <p class="subtitulo">Consulta, filtra y administra los pedidos realizados por los clientes.</p>

    <!-- Buscador -->
    <div class="buscador">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <input type="text" id="filtroPedido" placeholder="N° Pedido" style="width:120px;">
            <input type="text" id="filtroDocumento" placeholder="Documento del cliente...">
            <input type="text" id="filtroContacto" placeholder="Contacto (teléfono/email)...">

            <select id="registrosPorPagina" title="Registros por página">
                <option value="5" selected>5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-contenedor">
        <table id="tablaPedidos">
            <thead>
                <tr>
                    <th>Pedido #</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Contacto</th>
                    <th>Monto total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Paginador -->
    <div id="paginador" class="paginador"></div>
</div>

<script>
    let paginaActual = 1;
    let totalPaginas = 1;
    let registrosPorPagina = parseInt(document.getElementById('registrosPorPagina').value);

    const filtroPedido      = document.getElementById('filtroPedido');
    const filtroDocumento   = document.getElementById('filtroDocumento');
    const filtroContacto    = document.getElementById('filtroContacto');
    const selectRegistros   = document.getElementById('registrosPorPagina');
    const tbody             = document.querySelector('#tablaPedidos tbody');
    const paginador         = document.getElementById('paginador');

    // === Cargar pedidos ===
    async function cargarPedidos() {
        const params = new URLSearchParams({
            controller: 'venta',
            action: 'listar',
            pagina: paginaActual,
            registros: registrosPorPagina,
            pedido: filtroPedido.value.trim(),
            documento: filtroDocumento.value.trim(),
            contacto: filtroContacto.value.trim()
        });

        const resp = await fetch(`index.php?${params}`);
        const data = await resp.json();

        renderTabla(data.pedidos);
        totalPaginas = data.total_paginas;
        renderPaginador();
    }

    // === Render tabla ===
    function renderTabla(pedidos) {
        tbody.innerHTML = '';

        if (!pedidos || pedidos.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No se encontraron resultados</td></tr>`;
            return;
        }

        pedidos.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_pedido}</td>
                <td>${item.fecha_pedido}</td>
                <td>${item.nombre_persona} ${item.apellido_persona}</td>
                <td>${item.documento ?? '-'}</td>
                <td>${item.contacto ?? '-'}</td>
                <td>$${parseFloat(item.monto_total).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // === Render paginador ===
    function renderPaginador() {
        paginador.innerHTML = '';
        if (totalPaginas <= 1) return;

        for (let i = 1; i <= totalPaginas; i++) {
            paginador.innerHTML += `
                <button 
                    class="${i === paginaActual ? 'activo' : ''}" 
                    onclick="cambiarPagina(${i})">
                    ${i}
                </button>
            `;
        }
    }

    // === Cambiar página ===
    function cambiarPagina(num) {
        if (num === paginaActual) return;
        paginaActual = num;
        cargarPedidos();
    }

    // === Eventos ===
    [filtroPedido, filtroDocumento, filtroContacto].forEach(el => {
        el.addEventListener('input', () => {
            paginaActual = 1;
            cargarPedidos();
        });
    });

    selectRegistros.addEventListener('change', () => {
        registrosPorPagina = parseInt(selectRegistros.value);
        paginaActual = 1;
        cargarPedidos();
    });

    // === Inicio ===
    document.addEventListener('DOMContentLoaded', cargarPedidos);
</script>
