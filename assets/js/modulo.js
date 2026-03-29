import { mostrarExito, mostrarError, mostrarConfirmacion } from "./alertas.js";

document.addEventListener("DOMContentLoaded", () => {
    // Elementos comunes
    const inputBuscar = document.getElementById("buscar");
    const selectOrdenar = document.getElementById("ordenar");
    const selectCantidad = document.getElementById("registrosPorPagina");
    const tablaBody = document.querySelector("#tablaModulos tbody");
    const paginador = document.getElementById("paginador");
    const btnNuevo = document.getElementById("btnNuevo");

    // Modal NUEVO
    const modalNuevo = document.getElementById("modalNuevoModulo");
    const formNuevo = document.getElementById("formNuevoModulo");
    const inputDescripcion = document.getElementById("descripcion_modulo");
    const btnCancelarModal = document.getElementById("btnCancelarModal");

    // Modal EDITAR
    const modalEditar = document.getElementById("modalEditarModulo");
    const formEditar = document.getElementById("formEditarModulo");
    const inputEditarId = document.getElementById("editar_id_modulo");
    const inputEditarDescripcion = document.getElementById("editar_descripcion_modulo");
    const btnCancelarEditar = document.getElementById("btnCancelarEditar");

    let paginaActual = 1;

    /** -------------------------------
     * FUNCIONES DE MODALES
     * ------------------------------- */
    function abrirModalNuevo() {
        modalNuevo.style.display = "flex";
        inputDescripcion.focus();
    }

    function cerrarModalNuevo() {
        modalNuevo.style.display = "none";
        formNuevo.reset();
    }

    function abrirModalEditar(id, descripcion) {
        modalEditar.style.display = "flex";
        inputEditarId.value = id;
        inputEditarDescripcion.value = descripcion;
        inputEditarDescripcion.focus();
    }

    function cerrarModalEditar() {
        modalEditar.style.display = "none";
        formEditar.reset();
    }

    /** -------------------------------
     * CARGAR TABLA
     * ------------------------------- */
    function cargarDatos() {
        const buscar = inputBuscar.value.trim();
        const orden = selectOrdenar.value;
        const porPagina = selectCantidad.value;

        fetch("index.php?controller=Master&action=listarModulos", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `buscar=${encodeURIComponent(buscar)}&orden=${encodeURIComponent(orden)}&pagina=${paginaActual}&porPagina=${porPagina}`
        })
        .then(res => res.json())
        .then(data => {
            tablaBody.innerHTML = "";
            paginador.innerHTML = "";

            if (!data.datos || data.datos.length === 0) {
                tablaBody.innerHTML = "<tr><td colspan='2'>No se encontraron resultados</td></tr>";
                return;
            }

            data.datos.forEach(e => {
                const fila = `
                    <tr data-id="${e.id_modulo}">
                        <td>${e.descripcion_modulo}</td>
                        <td class="acciones">
                            <button class="btn-accion btn-editar" data-id="${e.id_modulo}" data-descripcion="${e.descripcion_modulo}">
                                <img src="assets/images/icons/edit.png" alt="Editar" class="icono-tabla">
                            </button>
                            <button class="btn-accion btn-eliminar" data-id="${e.id_modulo}">
                                <img src="assets/images/icons/delete.png" alt="Eliminar" class="icono-tabla">
                            </button>
                        </td>
                    </tr>
                `;
                tablaBody.insertAdjacentHTML("beforeend", fila);
            });

            // Eventos dinámicos
            document.querySelectorAll(".btn-editar").forEach(btn => {
                btn.addEventListener("click", () => {
                    abrirModalEditar(btn.dataset.id, btn.dataset.descripcion);
                });
            });

            document.querySelectorAll(".btn-eliminar").forEach(btn => {
                btn.addEventListener("click", () => eliminarModulo(btn.dataset.id));
            });

            // Paginador dinámico
            for (let i = 1; i <= data.total_paginas; i++) {
                const boton = document.createElement("button");
                boton.textContent = i;
                if (i === paginaActual) boton.classList.add("active");
                boton.addEventListener("click", () => {
                    paginaActual = i;
                    cargarDatos();
                });
                paginador.appendChild(boton);
            }
        })
        .catch(() => {
            tablaBody.innerHTML = "<tr><td colspan='2'>Error al cargar los datos</td></tr>";
        });
    }

    /** -------------------------------
     * CREAR MÓDULO
     * ------------------------------- */
    formNuevo.addEventListener("submit", e => {
        e.preventDefault();
        const descripcion = inputDescripcion.value.trim();

        if (descripcion === "") {
            mostrarError("Campo vacío", "Debe ingresar una descripción de módulo.");
            return;
        }

        if (descripcion.length < 3) {
            mostrarError("Nombre demasiado corto", "Debe tener al menos 3 letras.");
            return;
        }

        const datos = new URLSearchParams();
        datos.append("descripcion_modulo", descripcion);

        fetch("index.php?controller=Master&action=crearModulo", {
            method: "POST",
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarExito("Éxito", data.message);
                cerrarModalNuevo();
                cargarDatos();
            } else {
                mostrarError("Error", data.message);
            }
        })
        .catch(() => mostrarError("Error", "No se pudo conectar con el servidor"));
    });

    /** -------------------------------
     * EDITAR MÓDULO
     * ------------------------------- */
    formEditar.addEventListener("submit", e => {
        e.preventDefault();
        const id = inputEditarId.value;
        const descripcion = inputEditarDescripcion.value.trim();

        if (descripcion === "") {
            mostrarError("Campo vacío", "Debe ingresar una descripción de módulo.");
            return;
        }

        if (descripcion.length < 3) {
            mostrarError("Nombre demasiado corto", "Debe tener al menos 3 letras.");
            return;
        }

        const datos = new URLSearchParams();
        datos.append("id_modulo", id);
        datos.append("descripcion_modulo", descripcion);

        fetch("index.php?controller=Master&action=editarModulo", {
            method: "POST",
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarExito("Actualizado", data.message);
                cerrarModalEditar();
                cargarDatos();
            } else {
                mostrarError("Error", data.message);
            }
        })
        .catch(() => mostrarError("Error", "No se pudo conectar con el servidor"));
    });

    /** -------------------------------
     * ELIMINAR MÓDULO
     * ------------------------------- */
    async function eliminarModulo(id) {
        const confirmacion = await mostrarConfirmacion(
            "¿Eliminar módulo?",
            "Esta acción no se puede deshacer."
        );

        if (!confirmacion.isConfirmed) return;

        const datos = new URLSearchParams();
        datos.append("id_modulo", id);

        fetch("index.php?controller=Master&action=eliminarModulo", {
            method: "POST",
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarExito("Eliminado", data.message);
                cargarDatos();
            } else {
                mostrarError("Error", data.message);
            }
        })
        .catch(() => mostrarError("Error", "No se pudo conectar con el servidor"));
    }

    /** -------------------------------
     * EVENTOS DE INTERFAZ
     * ------------------------------- */
    btnNuevo.addEventListener("click", abrirModalNuevo);
    btnCancelarModal.addEventListener("click", cerrarModalNuevo);
    btnCancelarEditar.addEventListener("click", cerrarModalEditar);

    window.addEventListener("click", e => {
        if (e.target === modalNuevo) cerrarModalNuevo();
        if (e.target === modalEditar) cerrarModalEditar();
    });

    inputBuscar.addEventListener("keyup", () => {
        paginaActual = 1;
        cargarDatos();
    });

    selectOrdenar.addEventListener("change", () => {
        paginaActual = 1;
        cargarDatos();
    });

    selectCantidad.addEventListener("change", () => {
        paginaActual = 1;
        cargarDatos();
    });

    // Inicializar
    cargarDatos();
});

