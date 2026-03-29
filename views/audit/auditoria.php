<style>
/* Usamos exactamente la misma paleta y estilo base que tu módulo de productos */

/* Contenedor principal */
.contenedor-productos {
    width: 90%;
    max-width: 1250px;
    margin: 10px auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2b1a1f;
    background: #fff;
    padding: 30px 25px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Títulos */
.contenedor-productos h2 {
    text-align: left;
    margin-bottom: 10px;
    font-weight: 600;
    color: #7a1c4b;
    letter-spacing: 0.4px;
    font-size: 1.6rem;
}

.contenedor-productos .subtitulo {
    font-size: 0.95rem;
    color: #666;
    margin-bottom: 25px;
    line-height: 1.4;
}

/* Filtros */
.buscador {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.buscador input,
.buscador select {
    padding: 8px 10px;
    border: 1px solid #d94b8c;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease-in-out;
    color: #2b1a1f;
    background-color: #fff;
}

.buscador input:focus,
.buscador select:focus {
    border-color: #7a1c4b;
    box-shadow: 0 0 4px rgba(122, 28, 75, 0.3);
}

/* Tablas */
.tabla-contenedor {
    width: 100%;
    overflow-x: scroll;
    overflow-y: scroll;
    white-space: nowrap;
    max-height: 65vh;
    border: 1px solid #f0c8d8;
    border-radius: 10px;
}

.tabla-contenedor::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.tabla-contenedor::-webkit-scrollbar-thumb {
    background-color: #d94b8c;
    border-radius: 4px;
}

#tablaAuditoria {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

#tablaAuditoria thead {
    background-color: #7a1c4b;
    color: #fff;
}

#tablaAuditoria th {
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.3px;
}

#tablaAuditoria th:last-child {
    text-align: center;
}

#tablaAuditoria td {
    padding: 10px 8px;
    vertical-align: middle;
    font-size: 14px;
    border-bottom: 1px solid #f0c8d8;
}

#tablaAuditoria tbody tr:hover {
    background-color: #f9e2ec;
}

/* Paginador */
.paginador {
    margin-top: 20px;
    text-align: center;
}

.paginador button {
    margin: 2px;
    padding: 6px 12px;
    border: 1px solid #d94b8c;
    background: #fff;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
    color: #7a1c4b;
    font-weight: 500;
}

.paginador button:hover {
    background-color: #d94b8c;
    color: #fff;
    border-color: #d94b8c;
}

.paginador button.activo {
    background-color: #7a1c4b;
    color: #fff;
    border-color: #7a1c4b;
}
/* Contenedor del gráfico más compacto */
#graficoAcciones {
    max-width: 420px;
    height: 200px !important;
    margin: 15px auto;
    display: block;
}

/* Ajuste visual suave */
.chartjs-render-monitor {
    border: 1px solid #f0c8d8;
    border-radius: 10px;
    padding: 10px;
    background: #fff;
}

</style>
<div class="contenedor-productos">
    <h2>Auditoría del sistema</h2>
    <p class="subtitulo">Consulta los registros de acciones realizadas en el sistema.</p>

    <!-- Filtros -->
    <div class="buscador">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">

            <input type="text" id="filtroUsuario" placeholder="Filtrar por usuario">

            <select id="filtroAccion">
                <option value="">Todas las acciones</option>
                <option value="INSERT">INSERT</option>
                <option value="UPDATE">UPDATE</option>
                <option value="DELETE">DELETE</option>
                <option value="LOGIN">LOGIN</option>
                <option value="LOGOUT">LOGOUT</option>
            </select>

            <select id="filtroTabla">
                <option value="">Todas las tablas</option>
            </select>

            <input type="date" id="filtroDesde">
            <input type="date" id="filtroHasta">

            <select id="registrosPorPagina" title="Registros por página">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-contenedor">
        <table id="tablaAuditoria">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Tabla</th>
                    <th>Acción</th>
                    <th>Código de registro</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody><!-- dinámico --></tbody>
        </table>
    </div>

    <!-- Paginador -->
    <div id="paginador" class="paginador"></div>

    <!-- Contenedor del gráfico -->
<canvas id="graficoAcciones" width="400" height="180" style="margin-top:30px;"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</div>

<script>
// === Cargar estadísticas para gráfico ===
async function cargarGraficoAcciones() {
    try {
        const response = await fetch('index.php?controller=Audit&action=estadisticas');
        const { data } = await response.json();

        const ctx = document.getElementById('graficoAcciones').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['INSERT', 'UPDATE', 'DELETE'],
                datasets: [{
                    label: 'Cantidad de acciones',
                    data: [data.INSERT, data.UPDATE, data.DELETE]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

// Ejecutar después de cargar la tabla
cargarGraficoAcciones();
</script>


<script>
let paginaActual = 1;
let registrosPorPagina = 10;

const filtroUsuario   = document.getElementById('filtroUsuario');
const filtroAccion    = document.getElementById('filtroAccion');
const filtroTabla     = document.getElementById('filtroTabla');
const filtroDesde     = document.getElementById('filtroDesde');
const filtroHasta     = document.getElementById('filtroHasta');
const selectRegistros = document.getElementById('registrosPorPagina');
const tbody           = document.querySelector('#tablaAuditoria tbody');
const paginador       = document.getElementById('paginador');

// === Carga principal ===
async function cargarAuditoria() {
    const params = new URLSearchParams({
        controller: 'Audit',
        action: 'listar',
        pagina: paginaActual,
        registros: registrosPorPagina,
        usuario: filtroUsuario.value || '',
        accion: filtroAccion.value || '',
        tabla: filtroTabla.value || '',
        desde: filtroDesde.value || '',
        hasta: filtroHasta.value || ''
    });

    try {
        const response = await fetch(`index.php?${params.toString()}`);
        const data = await response.json();

        if (!data.estado || !data.registros.length) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#ac2424;">
                No se encontraron registros</td></tr>`;
            paginador.innerHTML = '';
            return;
        }

        renderTabla(data.registros);
        renderPaginador(data.total_paginas);
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#ac2424;">
            Error cargando registros</td></tr>`;
    }
}

// === Función para formatear fecha ===
function formatearFecha(fechaSQL) {
    if (!fechaSQL) return '-';
    const fecha = new Date(fechaSQL);
    if (isNaN(fecha.getTime())) return fechaSQL; // Si no es válida, devolver cruda

    const opciones = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return fecha.toLocaleString('es-AR', opciones).replace(',', '');
}

// === Render de tabla ===
function renderTabla(registros) {
    tbody.innerHTML = registros
        .map(reg => `
            <tr>
                <td>${reg.id_auditoria}</td>
                <td>${reg.usuario || '-'}</td>
                <td>${reg.tabla_afectada}</td>
                <td>${reg.accion}</td>
                <td>${reg.id_registro || '-'}</td>
                <td style="text-align:center;">${formatearFecha(reg.fecha_evento)}</td>
            </tr>
        `)
        .join('');
}

// === Paginador ===
function renderPaginador(totalPaginas) {
    let html = '';
    for (let i = 1; i <= totalPaginas; i++) {
        html += `<button class="${i === paginaActual ? 'activo' : ''}" 
                        onclick="cambiarPagina(${i})">${i}</button>`;
    }
    paginador.innerHTML = html;
}

function cambiarPagina(pag) {
    paginaActual = pag;
    cargarAuditoria();
}

// === Eventos ===
[filtroUsuario, filtroAccion, filtroTabla, filtroDesde, filtroHasta].forEach(el => {
    el.addEventListener('change', () => {
        paginaActual = 1;
        cargarAuditoria();
    });
});

selectRegistros.addEventListener('change', () => {
    registrosPorPagina = parseInt(selectRegistros.value);
    paginaActual = 1;
    cargarAuditoria();
});

// === Primera carga ===
cargarAuditoria();
</script>

