import { mostrarExito, mostrarError, mostrarConfirmacion } from "./alertas.js";

/* ==========================================================
   Lógica principal
   ========================================================== */
document.addEventListener("DOMContentLoaded", () => {
    // Elementos comunes
    const inputBuscar = document.getElementById("buscarSubCat");
    const selectOrdenar = document.getElementById("ordenarSubCat");
    const selectCantidad = document.getElementById("registrosPorPaginaSubCat");
    const filtroCategoria = document.getElementById("filtroCategoria");
    const tablaBody = document.querySelector("#tablaSubCategorias tbody");
    const paginador = document.getElementById("paginadorSubCat");
    const btnNuevo = document.getElementById("btnNuevaSubCat");

    // Modal NUEVO
    const modalNuevo = document.getElementById("modalNuevaSubCat");
    const formNuevo = document.getElementById("formNuevaSubCat");
    const inputNombre = document.getElementById("nombre_sub_categoria");
    const inputCantidad = document.getElementById("cant_sub_categoria");
    const selectCategoriaNuevo = document.getElementById("id_categoria");
    const btnCancelarModal = document.getElementById("btnCancelarModalSubCat");

    // Modal EDITAR
    const modalEditar = document.getElementById("modalEditarSubCat");
    const formEditar = document.getElementById("formEditarSubCat");
    const inputEditarId = document.getElementById("editar_id_sub_categoria");
    const inputEditarNombre = document.getElementById("editar_nombre_sub_categoria");
    const inputEditarCantidad = document.getElementById("editar_cant_sub_categoria");
    const selectCategoriaEditar = document.getElementById("editar_id_categoria");
    const btnCancelarEditar = document.getElementById("btnCancelarEditarSubCat");

    let paginaActual = 1;

    /* ==========================================================
       Cargar combo de categorías activas (para filtro y formularios)
       ========================================================== */
    function cargarCategoriasActivas() {
        fetch("index.php?controller=Master&action=listarCategoriasActivas")
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const categorias = data.data;

                    // Filtro principal
                    filtroCategoria.innerHTML = `<option value="0">-- Todas las categorías --</option>`;
                    categorias.forEach(cat => {
                        const option = document.createElement("option");
                        option.value = cat.id_categoria;
                        option.textContent = cat.nombre_categoria;
                        filtroCategoria.appendChild(option);
                    });

                    // Formularios (nuevo y editar)
                    selectCategoriaNuevo.innerHTML = `<option value="">Seleccione una categoría...</option>`;
                    selectCategoriaEditar.innerHTML = `<option value="">Seleccione una categoría...</option>`;
                    categorias.forEach(cat => {
                        const opt1 = optionFactory(cat);
                        const opt2 = optionFactory(cat);
                        selectCategoriaNuevo.appendChild(opt1);
                        selectCategoriaEditar.appendChild(opt2);
                    });
                } else {
                    console.warn("No hay categorías activas.");
                }
            })
            .catch(() => mostrarError("Error", "No se pudieron cargar las categorías activas."));
    }

    const optionFactory = (cat) => {
        const opt = document.createElement("option");
        opt.value = cat.id_categoria;
        opt.textContent = cat.nombre_categoria;
        return opt;
    };

    /* ==========================================================
       CARGAR TABLA DE SUBCATEGORÍAS
       ========================================================== */
    function cargarDatos() {
        const buscar = inputBuscar.value.trim();
        const orden = selectOrdenar.value;
        const porPagina = selectCantidad.value;
        const idCategoria = filtroCategoria.value;

        const bodyData = new URLSearchParams();
        bodyData.append("buscar", buscar);
        bodyData.append("orden", orden);
        bodyData.append("pagina", paginaActual);
        bodyData.append("porPagina", porPagina);
        bodyData.append("id_categoria", idCategoria);

        fetch("index.php?controller=Master&action=listarSubCategorias", {
            method: "POST",
            body: bodyData
        })
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = "";
                paginador.innerHTML = "";

                if (!data.success || !data.data?.length) {
                    tablaBody.innerHTML = "<tr><td colspan='4'>No se encontraron subcategorías.</td></tr>";
                    return;
                }

                data.data.forEach(sub => {
                    const fila = `
                        <tr data-id="${sub.id_sub_categoria}">
                            <td>${sub.nombre_categoria || "(Sin categoría)"}</td>
                            <td>${sub.nombre_sub_categoria}</td>
                            <td>${sub.cant_sub_categoria}</td>
                            <td class="acciones">
                                <button class="btn-accion btn-editar" 
                                    data-id="${sub.id_sub_categoria}" 
                                    data-nombre="${sub.nombre_sub_categoria}" 
                                    data-cantidad="${sub.cant_sub_categoria}" 
                                    data-categoria="${sub.id_categoria}">
                                    <img src="assets/images/icons/edit.png" alt="Editar" class="icono-tabla">
                                </button>
                                <button class="btn-accion btn-eliminar" data-id="${sub.id_sub_categoria}">
                                    <img src="assets/images/icons/delete.png" alt="Eliminar" class="icono-tabla">
                                </button>
                            </td>
                        </tr>`;
                    tablaBody.insertAdjacentHTML("beforeend", fila);
                });

                // Eventos dinámicos
                document.querySelectorAll(".btn-editar").forEach(btn =>
                    btn.addEventListener("click", () =>
                        abrirModalEditar(
                            btn.dataset.id,
                            btn.dataset.nombre,
                            btn.dataset.cantidad,
                            btn.dataset.categoria
                        )
                    )
                );
                document.querySelectorAll(".btn-eliminar").forEach(btn =>
                    btn.addEventListener("click", () => eliminarSubCategoria(btn.dataset.id))
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
                tablaBody.innerHTML = "<tr><td colspan='4'>Error al cargar los datos.</td></tr>";
            });
    }

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
    };

    const abrirModalEditar = (id, nombre, cantidad, idCategoria) => {
        modalEditar.style.display = "flex";
        inputEditarId.value = id;
        inputEditarNombre.value = nombre;
        inputEditarCantidad.value = cantidad;
        selectCategoriaEditar.value = idCategoria;
        inputEditarNombre.focus();
    };

    const cerrarModalEditar = () => {
        modalEditar.style.display = "none";
        formEditar.reset();
    };

    /* ==========================================================
       CREAR SUBCATEGORÍA
       ========================================================== */
    formNuevo.addEventListener("submit", e => {
        e.preventDefault();

        const nombre = inputNombre.value.trim();
        const cantidad = parseInt(inputCantidad.value);
        const categoria = selectCategoriaNuevo.value;

        if (nombre.length < 3) {
            mostrarError("Campo inválido", "El nombre debe tener al menos 3 caracteres.");
            return;
        }

        if (isNaN(cantidad) || cantidad <= 0) {
            mostrarError("Campo inválido", "La cantidad debe ser mayor a cero.");
            return;
        }

        if (!categoria) {
            mostrarError("Campo inválido", "Debe seleccionar una categoría.");
            return;
        }

        const formData = new FormData(formNuevo);

        fetch("index.php?controller=Master&action=crearSubCategoria", {
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
            .catch(() => mostrarError("Error", "No se pudo conectar con el servidor."));
    });

    /* ==========================================================
       EDITAR SUBCATEGORÍA
       ========================================================== */
    formEditar.addEventListener("submit", e => {
        e.preventDefault();

        const nombre = inputEditarNombre.value.trim();
        const cantidad = parseInt(inputEditarCantidad.value);
        const categoria = selectCategoriaEditar.value;

        if (nombre.length < 3) {
            mostrarError("Campo inválido", "El nombre debe tener al menos 3 caracteres.");
            return;
        }

        if (isNaN(cantidad) || cantidad <= 0) {
            mostrarError("Campo inválido", "La cantidad debe ser mayor a cero.");
            return;
        }

        if (!categoria) {
            mostrarError("Campo inválido", "Debe seleccionar una categoría.");
            return;
        }

        const formData = new FormData(formEditar);

        fetch("index.php?controller=Master&action=editarSubCategoria", {
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
            .catch(() => mostrarError("Error", "No se pudo conectar con el servidor."));
    });

    /* ==========================================================
       ELIMINAR SUBCATEGORÍA (baja lógica)
       ========================================================== */
    async function eliminarSubCategoria(id) {
        const confirmacion = await mostrarConfirmacion(
            "¿Eliminar subcategoría?",
            "Esta acción no se puede deshacer."
        );

        if (!confirmacion.isConfirmed) return;

        const datos = new URLSearchParams();
        datos.append("id_sub_categoria", id);

        fetch("index.php?controller=Master&action=eliminarSubCategoria", {
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
            .catch(() => mostrarError("Error", "No se pudo conectar con el servidor."));
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

    filtroCategoria.addEventListener("change", () => {
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

    // Inicialización
    cargarCategoriasActivas();
    cargarDatos();
});
