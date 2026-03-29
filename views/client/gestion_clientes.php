<style>
/* Reutilizamos la misma base que productos, con adaptación de nombres */

/* Contenedor general */
.contenedor-clientes {
    width: 90%;
    max-width: 1250px;
    margin: 10px auto;
    background: #fff;
    padding: 30px 25px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2b1a1f;
}
.contenedor-clientes h2 {
    color: #7a1c4b;
    font-size: 1.6rem;
    margin-bottom: 10px;
}

/* Buscador y filtros */
.buscador-clientes {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.buscador-clientes input,
.buscador-clientes select {
    padding: 7px 10px;
    border: 1px solid #d94b8c;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
}
.buscador-clientes input:focus,
.buscador-clientes select:focus {
    border-color: #7a1c4b;
    box-shadow: 0 0 4px rgba(122, 28, 75, 0.3);
}

/* Tabla */
.tabla-contenedor {
    width: 100%;
    overflow: auto;
    border: 1px solid #f0c8d8;
    border-radius: 10px;
    max-height: 65vh;
}
#tablaClientes {
    width: 100%;
    border-collapse: collapse;
}
#tablaClientes thead {
    background-color: #7a1c4b;
    color: #fff;
}
#tablaClientes th, #tablaClientes td {
    padding: 10px;
    font-size: 14px;
    border-bottom: 1px solid #f0c8d8;
    white-space: nowrap;
}
#tablaClientes tbody tr:hover {
    background-color: #f9e2ec;
}

/* Botones de acción */
.btn-accion {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 3px;
    margin: 0 3px;
    transition: transform .15s ease;
}
.btn-accion:hover {
    transform: scale(1.1);
    opacity: .8;
}
.icono-tabla {
    width: 20px;
    height: 20px;
}

/* Paginador */
.paginador {
    margin-top: 15px;
    text-align: center;
}
.paginador button {
    padding: 6px 12px;
    margin: 2px;
    border: 1px solid #d94b8c;
    background: #fff;
    border-radius: 5px;
    cursor: pointer;
    color: #7a1c4b;
}
.paginador button.activo,
.paginador button:hover {
    background-color: #7a1c4b;
    color: #fff;
}
</style>

<div id="contenedorClientes" class="contenedor-clientes">
    <h2>Gestión de clientes</h2>
    <p class="subtitulo">
        Listado de clientes registrados, estado de su cuenta, actividad y opciones de administración.
    </p>

    <!-- Buscador y filtros -->
    <div class="buscador-clientes">
        <div class="filtros-clientes" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <input type="text" id="buscarCliente" placeholder="Buscar por nombre, usuario o documento...">

            <select id="filtroEstadoUsuario">
                <option value="">Estado usuario</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>

            <select id="filtroCuentaActivada">
                <option value="">Cuenta activada</option>
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>

            <select id="filtroEstadoCliente">
                <option value="">Estado cliente</option>
                <option value="1">Registrado</option>
                <option value="2">Eliminado</option>
            </select>

            <select id="registrosPorPagina">
                <option value="5" selected>5</option>
                <option value="10">10</option>
                <option value="20">20</option>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="tabla-contenedor">
        <table id="tablaClientes">
            <thead>
                <tr>
                    <th>Nombre y apellido</th>
                    <th>Documento</th>
                    <th>Correo electrónico</th>
                    <th>Teléfono</th>
                    <th>Usuario</th>
                    <th>Fecha registro</th>
                    <th>Cuenta activada</th>
                    <th>Estado usuario</th>
                    <th>Estado cliente</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" style="text-align:center; padding:15px;">
                        Cargando datos...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Paginador -->
    <div id="paginadorClientes" class="paginador"></div>
</div>

<script>// assets/js/clientes.js

document.addEventListener('DOMContentLoaded', () => {

    let paginaActual = 1;

    // Elementos del DOM
    const buscarInput           = document.querySelector('#buscarCliente');
    const filtroEstadoUsuario   = document.querySelector('#filtroEstadoUsuario');
    const filtroCuentaActivada  = document.querySelector('#filtroCuentaActivada');
    const filtroEstadoCliente   = document.querySelector('#filtroEstadoCliente');
    const registrosPorPagina    = document.querySelector('#registrosPorPagina');
    const tablaBody             = document.querySelector('#tablaClientes tbody');
    const paginador             = document.querySelector('#paginadorClientes');

    // Cargar clientes inicial
    cargarClientes();

    // Eventos de filtros
    buscarInput.addEventListener('input', () => cambiarPagina(1));
    filtroEstadoUsuario.addEventListener('change', () => cambiarPagina(1));
    filtroCuentaActivada.addEventListener('change', () => cambiarPagina(1));
    filtroEstadoCliente.addEventListener('change', () => cambiarPagina(1));
    registrosPorPagina.addEventListener('change', () => cambiarPagina(1));

    function cambiarPagina(nuevaPagina) {
        paginaActual = nuevaPagina;
        cargarClientes();
    }

    function cargarClientes() {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align:center;">Cargando...</td>
            </tr>
        `;

        const params = new URLSearchParams({
            pagina: paginaActual,
            registrosPorPagina: registrosPorPagina.value,
            buscar: buscarInput.value.trim(),
            estado_usuario: filtroEstadoUsuario.value,
            cuenta_activada: filtroCuentaActivada.value,
            estado_cliente: filtroEstadoCliente.value
        });

        fetch(`index.php?controller=Cliente&action=obtenerClientes&${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderizarTabla(data.data);
                    renderizarPaginador(data.totalPaginas);
                } else {
                    tablaBody.innerHTML = `
                        <tr>
                            <td colspan="10" style="text-align:center;">${data.message || 'No se encontraron registros'}</td>
                        </tr>`;
                }
            })
            .catch(err => {
                console.error('Error cargando clientes:', err);
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align:center; color:red;">
                            Error al cargar datos
                        </td>
                    </tr>`;
            });
    }

function renderizarTabla(clientes) {
    tablaBody.innerHTML = '';

    if (!clientes || clientes.length === 0) {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align:center;">No hay registros</td>
            </tr>`;
        return;
    }

    clientes.forEach(cliente => {
        const fila = document.createElement('tr');

        fila.innerHTML = `
            <td>${cliente.nombre_completo}</td>
            <td>${cliente.documento}</td>
            <td>${cliente.email || '-'}</td>
            <td>${cliente.telefono || '-'}</td>
            <td>${cliente.usuario}</td>
            <td>${cliente.fecha_registro}</td>
            <td>${cliente.cuenta_activada == 1 ? 'Sí' : 'No'}</td>
            <td>${cliente.estado_usuario == 1 ? 'Activo' : 'Inactivo'}</td>
            <td>${cliente.estado_cliente == 1 ? 'Registrado' : 'Eliminado'}</td>
            <td style="text-align:center;">
                <button class="btn-accion" title="Ver historial" onclick="verHistorial(${cliente.id_cliente})">
                    <img src="assets/images/icons/historial.png" class="icono-tabla">
                </button>
                ${cliente.estado_cliente == 1 
                    ? `<button class="btn-accion" title="Dar de baja" onclick="darDeBaja(${cliente.id_cliente})">
                            <img src="assets/images/icons/delete.png" class="icono-tabla">
                       </button>` 
                    : ''
                }
            </td>
        `;
        tablaBody.appendChild(fila);
    });
}


    function renderizarPaginador(totalPaginas) {
        paginador.innerHTML = '';

        if (!totalPaginas || totalPaginas <= 1) return;

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.classList.toggle('activo', i === paginaActual);
            btn.addEventListener('click', () => cambiarPagina(i));
            paginador.appendChild(btn);
        }
    }

    // 🔹 Redirección al historial (ADMIN)
    window.verHistorial = function(idCliente) {
        Swal.fire({
            title: 'Ver historial',
            text: '¿Quieres consultar el historial de compras de este cliente?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, ver historial',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#7a1c4b',
            cancelButtonColor: '#aaa'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `index.php?controller=Cliente&action=verSeccionHistorialDesdeAdmin&id=${idCliente}`;
            }
        });
    };

    // 🔸 Baja lógica
    window.darDeBaja = function(idCliente) {
        Swal.fire({
            title: 'Confirmar baja',
            text: 'Esta acción elimina un cliente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Dar de baja',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`index.php?controller=Cliente&action=darDeBaja`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_cliente: idCliente })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cliente dado de baja correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        cargarClientes();
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo dar de baja', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error en baja lógica:', err);
                    Swal.fire('Error', 'Error en la conexión con el servidor', 'error');
                });
            }
        });
    };

});

</script>
