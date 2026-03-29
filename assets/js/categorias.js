import { mostrarExito, mostrarError, mostrarConfirmacion } from "./alertas.js";

/* ==========================================================
   Vista previa de imágenes (nueva y editar)
   ========================================================== */
document.addEventListener("change", e => {
    const fileInput = e.target;

    // Nueva categoría
    if (fileInput.id === "imagen_categoria") {
        const file = fileInput.files[0];
        const preview = document.getElementById("imagenPreviewNueva");
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                preview.src = ev.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    }

    // Editar categoría
    if (fileInput.id === "editar_imagen_categoria") {
        const file = fileInput.files[0];
        const preview = document.getElementById("imagen_actual_preview");
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                preview.src = ev.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    }
});

/* ==========================================================
 Lógica principal
   ========================================================== */
document.addEventListener("DOMContentLoaded", () => {
    // Elementos comunes
    const inputBuscar = document.getElementById("buscar");
    const selectOrdenar = document.getElementById("ordenar");
    const selectCantidad = document.getElementById("registrosPorPagina");
    const tablaBody = document.querySelector("#tablaCategorias tbody");
    const paginador = document.getElementById("paginador");
    const btnNuevo = document.getElementById("btnNuevo");

    // Modal NUEVO
    const modalNuevo = document.getElementById("modalNuevaCategoria");
    const formNuevo = document.getElementById("formNuevaCategoria");
    const inputNombre = document.getElementById("nombre_categoria");
    const btnCancelarModal = document.getElementById("btnCancelarModal");

    // Modal EDITAR
    const modalEditar = document.getElementById("modalEditarCategoria");
    const formEditar = document.getElementById("formEditarCategoria");
    const inputEditarId = document.getElementById("editar_id_categoria");
    const inputEditarNombre = document.getElementById("editar_nombre_categoria");
    const btnCancelarEditar = document.getElementById("btnCancelarEditar");
    const imgPreview = document.getElementById("imagen_actual_preview");

    let paginaActual = 1;

    /* ==========================================================
       FUNCIONES DE MODALES
       ========================================================== */
    const abrirModalNuevo = () => {
        modalNuevo.style.display = "flex";
        inputNombre.focus();
    };

    const cerrarModalNuevo = () => {
        modalNuevo.style.display = "none";
        formNuevo.reset();
        document.getElementById("imagenPreviewNueva").style.display = "none";
    };

    const abrirModalEditar = (id, nombre, imagen) => {
        modalEditar.style.display = "flex";
        inputEditarId.value = id;
        inputEditarNombre.value = nombre;
        inputEditarNombre.focus();

        if (imagen) {
            imgPreview.src = imagen;
            imgPreview.style.display = "inline-block";
        } else {
            imgPreview.style.display = "none";
        }
    };

    const cerrarModalEditar = () => {
        modalEditar.style.display = "none";
        formEditar.reset();
        imgPreview.style.display = "none";
    };

    /* ==========================================================
     CARGAR TABLA (solo categorías activas)
       ========================================================== */
    function cargarDatos() {
        const buscar = inputBuscar.value.trim();
        const orden = selectOrdenar.value;
        const porPagina = selectCantidad.value;

        fetch("index.php?controller=Master&action=listarCategorias", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `buscar=${encodeURIComponent(buscar)}&orden=${encodeURIComponent(orden)}&pagina=${paginaActual}&porPagina=${porPagina}`
        })
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = "";
                paginador.innerHTML = "";

                if (!data.success || !data.data?.length) {
                    tablaBody.innerHTML = "<tr><td colspan='3'>No se encontraron categorías activas.</td></tr>";
                    return;
                }

                // Renderizar filas
                data.data.forEach(cat => {
                    const fila = `
                        <tr data-id="${cat.id_categoria}">
                            <td>${cat.nombre_categoria}</td>
                            <td>${cat.imagen_categoria
                            ? `<img src="${cat.imagen_categoria}" alt="Imagen" style="max-width:60px; border-radius:5px;">`
                            : "—"}</td>
                            <td class="acciones" style="text-align:center;">
                                <button class="btn-accion btn-editar" 
                                    data-id="${cat.id_categoria}" 
                                    data-nombre="${cat.nombre_categoria}" 
                                    data-imagen="${cat.imagen_categoria || ''}">
                                    <img src="assets/images/icons/edit.png" alt="Editar" class="icono-tabla">
                                </button>
                                <button class="btn-accion btn-eliminar" data-id="${cat.id_categoria}">
                                    <img src="assets/images/icons/delete.png" alt="Eliminar" class="icono-tabla">
                                </button>
                            </td>
                        </tr>`;
                    tablaBody.insertAdjacentHTML("beforeend", fila);
                });

                // Eventos dinámicos
                document.querySelectorAll(".btn-editar").forEach(btn =>
                    btn.addEventListener("click", () =>
                        abrirModalEditar(btn.dataset.id, btn.dataset.nombre, btn.dataset.imagen)
                    )
                );
                document.querySelectorAll(".btn-eliminar").forEach(btn =>
                    btn.addEventListener("click", () => eliminarCategoria(btn.dataset.id))
                );

                // Paginador dinámico
                const totalPaginas = Math.ceil(data.total / data.porPagina);
                for (let i = 1; i <= totalPaginas; i++) {
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
                tablaBody.innerHTML = "<tr><td colspan='3'>Error al cargar los datos.</td></tr>";
            });
    }

    /* ==========================================================
       CREAR CATEGORÍA (FormData)
       ========================================================== */
    formNuevo.addEventListener("submit", e => {
        e.preventDefault();
        const nombre = inputNombre.value.trim();

        if (nombre.length < 3) {
            mostrarError("Campo inválido", "El nombre debe tener al menos 3 caracteres.");
            return;
        }

        const formData = new FormData(formNuevo);

        fetch("index.php?controller=Master&action=crearCategoria", {
            method: "POST",
            body: formData
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

    /* ==========================================================
       EDITAR CATEGORÍA (FormData)
       ========================================================== */
    formEditar.addEventListener("submit", e => {
        e.preventDefault();
        const nombre = inputEditarNombre.value.trim();

        if (nombre.length < 3) {
            mostrarError("Campo inválido", "El nombre debe tener al menos 3 caracteres.");
            return;
        }

        const formData = new FormData(formEditar);

        fetch("index.php?controller=Master&action=editarCategoria", {
            method: "POST",
            body: formData
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

    /* ==========================================================
       ELIMINAR CATEGORÍA (baja lógica)
       ========================================================== */
    async function eliminarCategoria(id) {
        const confirmacion = await mostrarConfirmacion(
            "¿Eliminar categoría?", "Esta acción no se puede deshacer."
        );

        if (!confirmacion.isConfirmed) return;

        const datos = new URLSearchParams();
        datos.append("id_categoria", id);

        fetch("index.php?controller=Master&action=eliminarCategoria", {
            method: "POST",
            body: datos
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarExito("Eliminada", data.message);
                    cargarDatos();
                } else {
                    mostrarError("Error", data.message);
                }
            })
            .catch(() => mostrarError("Error", "No se pudo conectar con el servidor"));
    }

    /* ==========================================================
       EVENTOS DE INTERFAZ
       ========================================================== */
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

    // Inicializar tabla
    cargarDatos();
});
