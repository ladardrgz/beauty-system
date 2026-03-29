<?php
class MasterController
{
    private $model;

    public function __construct()
    {
        $this->model = new MasterModel();
    }

    /* ===================================================
    SECCIÓN: ESTADOS LÓGICOS
    =================================================== */
    public function verFrmEstado()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_estados.php';
        require_once 'views/layouts/main.php';
    }

    public function listarEstados()
    {
        $buscar    = trim($_POST['buscar'] ?? '');
        $orden     = strtoupper($_POST['orden'] ?? 'ASC');
        $pagina    = max(1, intval($_POST['pagina'] ?? 1));
        $porPagina = isset($_POST['porPagina']) ? intval($_POST['porPagina']) : 10;

        $resultado = $this->model->obtenerEstados($buscar, $orden, $pagina, $porPagina);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    public function crearEstado()
    {
        $nombre = trim($_POST['nombre_estado'] ?? '');

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($this->model->existeEstado($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un estado con ese nombre.']);
            return;
        }

        $resultado = $this->model->insertarEstado($nombre);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Estado creado correctamente.'
                : 'Error al guardar el estado.'
        ]);
    }

    public function editarEstado()
    {
        $id     = intval($_POST['id_estado_logico'] ?? 0);
        $nombre = trim($_POST['nombre_estado'] ?? '');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($this->model->existeEstado($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro estado con ese nombre.']);
            return;
        }

        $resultado = $this->model->actualizarEstado($id, $nombre);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Estado actualizado correctamente.'
                : 'No se pudo actualizar el estado.'
        ]);
    }

    public function eliminarEstado()
    {
        $id = intval($_POST['id_estado_logico'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Código inválido.']);
            return;
        }

        if ($this->model->estadoEnUso($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar este estado porque está siendo utilizado en otros registros.'
            ]);
            return;
        }

        $resultado = $this->model->eliminarEstado($id);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Estado eliminado correctamente.'
                : 'No se pudo eliminar el estado.'
        ]);
    }   // 👈 ESTA llave cierra correctamente el bloque de ESTADOS


    /* ===================================================
    SECCIÓN: PAÍSES
    =================================================== */

    public function verFrmPais()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_pais.php';
        require_once 'views/layouts/main.php';
    }

    public function listarPaises()
    {
        $buscar    = trim($_POST['buscar'] ?? '');
        $orden     = strtoupper($_POST['orden'] ?? 'ASC');
        $pagina    = max(1, intval($_POST['pagina'] ?? 1));
        $porPagina = isset($_POST['porPagina']) ? intval($_POST['porPagina']) : 10;

        $resultado = $this->model->obtenerPaises($buscar, $orden, $pagina, $porPagina);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    public function crearPais()
    {
        $nombre = trim($_POST['nombre_pais'] ?? '');

        if (strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($this->model->existePais($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un país con ese nombre.']);
            return;
        }

        $resultado = $this->model->insertarPais($nombre);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'País creado correctamente.'
                : 'Error al guardar el país.'
        ]);
    }

    public function editarPais()
    {
        $id     = intval($_POST['id_pais'] ?? 0);
        $nombre = trim($_POST['nombre_pais'] ?? '');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if (strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($this->model->paisEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede modificar este país porque está en uso por otras tablas.']);
            return;
        }

        if ($this->model->existePais($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro país con ese nombre.']);
            return;
        }

        $resultado = $this->model->actualizarPais($id, $nombre);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'País actualizado correctamente.'
                : 'No se pudo actualizar el país.'
        ]);
    }

    public function eliminarPais()
    {
        $id = intval($_POST['id_pais'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Código inválido.']);
            return;
        }

        if ($this->model->paisEnUso($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar este país porque está siendo utilizado por alguna provincia.'
            ]);
            return;
        }

        $resultado = $this->model->eliminarPais($id);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'País eliminado correctamente.'
                : 'No se pudo eliminar el país.'
        ]);
    }

    /* ===================================================
    SECCIÓN: PROVINCIAS
    =================================================== */

    // 📄 Cargar vista principal del módulo
    public function verFrmProvincia()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_provincia.php';
        require_once 'views/layouts/main.php';
    }

    // 📋 Listar provincias con paginación y filtro
    public function listarProvincias()
    {
        $buscar    = trim($_POST['buscar'] ?? '');
        $orden     = strtoupper($_POST['orden'] ?? 'ASC');
        $pagina    = max(1, intval($_POST['pagina'] ?? 1));
        $porPagina = isset($_POST['porPagina']) ? intval($_POST['porPagina']) : 10;

        $resultado = $this->model->obtenerProvincias($buscar, $orden, $pagina, $porPagina);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    // ➕ Crear nueva provincia
    public function crearProvincia()
    {
        $nombre  = trim($_POST['nombre_provincia'] ?? '');
        $id_pais = intval($_POST['id_pais'] ?? 0);

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_pais <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un país válido.']);
            return;
        }

        if ($this->model->existeProvincia($nombre, $id_pais)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una provincia con ese nombre en este país.']);
            return;
        }

        $resultado = $this->model->insertarProvincia($nombre, $id_pais);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Provincia creada correctamente.'
                : 'Error al guardar la provincia.'
        ]);
    }

    // ✏️ Editar provincia existente
    public function editarProvincia()
    {
        $id       = intval($_POST['id_provincia'] ?? 0);
        $nombre   = trim($_POST['nombre_provincia'] ?? '');
        $id_pais  = intval($_POST['id_pais'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_pais <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un país válido.']);
            return;
        }

        if ($this->model->existeProvincia($nombre, $id_pais, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra provincia con ese nombre en este país.']);
            return;
        }

        $resultado = $this->model->actualizarProvincia($id, $nombre, $id_pais);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Provincia actualizada correctamente.'
                : 'No se pudo actualizar la provincia.'
        ]);
    }

    // 🗑️ Eliminar provincia
    public function eliminarProvincia()
    {
        $id = intval($_POST['id_provincia'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Código inválido.']);
            return;
        }

        if ($this->model->provinciaEnUso($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar esta provincia porque está siendo utilizada en localidades.'
            ]);
            return;
        }

        $resultado = $this->model->eliminarProvincia($id);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Provincia eliminada correctamente.'
                : 'No se pudo eliminar la provincia.'
        ]);
    }

    // Listar países (para combo select)
    public function listarPaisesSelect()
    {
        $resultado = $this->model->obtenerPaisesSelect();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    /* ===================================================
    SECCIÓN: LOCALIDADES
    =================================================== */

    // 📄 Cargar la vista principal del módulo
    public function verFrmLocalidad()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_localidad.php';
        require_once 'views/layouts/main.php';
    }

    // 📋 Listar localidades con paginación, búsqueda y provincia asociada
    public function listarLocalidades()
    {
        $buscar    = trim($_POST['buscar'] ?? '');
        $orden     = strtoupper($_POST['orden'] ?? 'ASC');
        $pagina    = max(1, intval($_POST['pagina'] ?? 1));
        $porPagina = isset($_POST['porPagina']) ? intval($_POST['porPagina']) : 10;

        $resultado = $this->model->obtenerLocalidades($buscar, $orden, $pagina, $porPagina);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    // ➕ Crear nueva localidad
    public function crearLocalidad()
    {
        $nombre       = trim($_POST['nombre_localidad'] ?? '');
        $id_provincia = intval($_POST['id_provincia'] ?? 0);

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_provincia <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una provincia válida.']);
            return;
        }

        if ($this->model->existeLocalidad($nombre, $id_provincia)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una localidad con ese nombre en esta provincia.']);
            return;
        }

        $resultado = $this->model->insertarLocalidad($nombre, $id_provincia);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Localidad creada correctamente.'
                : 'Error al guardar la localidad.'
        ]);
    }

    // Editar localidad existente
    public function editarLocalidad()
    {
        $id           = intval($_POST['id_localidad'] ?? 0);
        $nombre       = trim($_POST['nombre_localidad'] ?? '');
        $id_provincia = intval($_POST['id_provincia'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_provincia <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una provincia válida.']);
            return;
        }

        if ($this->model->existeLocalidad($nombre, $id_provincia, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra localidad con ese nombre en esta provincia.']);
            return;
        }

        $resultado = $this->model->actualizarLocalidad($id, $nombre, $id_provincia);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Localidad actualizada correctamente.'
                : 'No se pudo actualizar la localidad.'
        ]);
    }

    // Eliminar localidad
    public function eliminarLocalidad()
    {
        $id = intval($_POST['id_localidad'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Código inválido.']);
            return;
        }

        if ($this->model->localidadEnUso($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar esta localidad porque tiene barrios asociados.'
            ]);
            return;
        }

        $resultado = $this->model->eliminarLocalidad($id);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Localidad eliminada correctamente.'
                : 'No se pudo eliminar la localidad.'
        ]);
    }

    // Listar provincias para combo select
    public function listarProvinciasSelect()
    {
        $resultado = $this->model->obtenerProvinciasSelect();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    /* ===================================================
    SECCIÓN: BARRIOS
    =================================================== */

    // 📄 Cargar vista principal del módulo
    public function verFrmBarrio()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_barrio.php';
        require_once 'views/layouts/main.php';
    }

    // Listar barrios con paginación, búsqueda y orden
    public function listarBarrios()
    {
        $buscar    = trim($_POST['buscar'] ?? '');
        $orden     = strtoupper($_POST['orden'] ?? 'ASC');
        $pagina    = max(1, intval($_POST['pagina'] ?? 1));
        $porPagina = isset($_POST['porPagina']) ? intval($_POST['porPagina']) : 10;

        $resultado = $this->model->obtenerBarrios($buscar, $orden, $pagina, $porPagina);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    // Crear nuevo barrio
    public function crearBarrio()
    {
        $nombre        = trim($_POST['nombre_barrio'] ?? '');
        $id_localidad  = intval($_POST['id_localidad'] ?? 0);

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_localidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una localidad válida.']);
            return;
        }

        if ($this->model->existeBarrio($nombre, $id_localidad)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un barrio con ese nombre en esta localidad.']);
            return;
        }

        $resultado = $this->model->insertarBarrio($nombre, $id_localidad);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Barrio creado correctamente.'
                : 'Error al guardar el barrio.'
        ]);
    }

    // Editar barrio existente
    public function editarBarrio()
    {
        $id           = intval($_POST['id_barrio'] ?? 0);
        $nombre       = trim($_POST['nombre_barrio'] ?? '');
        $id_localidad = intval($_POST['id_localidad'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        if ($id_localidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una localidad válida.']);
            return;
        }

        if ($this->model->existeBarrio($nombre, $id_localidad, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro barrio con ese nombre en esta localidad.']);
            return;
        }

        $resultado = $this->model->actualizarBarrio($id, $nombre, $id_localidad);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Barrio actualizado correctamente.'
                : 'No se pudo actualizar el barrio.'
        ]);
    }

    // Eliminar barrio
    public function eliminarBarrio()
    {
        $id = intval($_POST['id_barrio'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Código inválido.']);
            return;
        }

        if ($this->model->barrioEnUso($id)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar este barrio porque está siendo utilizado en domicilios.'
            ]);
            return;
        }

        $resultado = $this->model->eliminarBarrio($id);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? 'Barrio eliminado correctamente.'
                : 'No se pudo eliminar el barrio.'
        ]);
    }

    // Listar localidades para los combos <select>
    public function listarLocalidadesSelect()
    {
        $resultado = $this->model->obtenerLocalidadesSelect();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    /* ===================================================
    SECCIÓN: TIPO DE DOCUMENTO
    =================================================== */

    // Cargar vista principal del módulo
    public function verFrmTipoDoc()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_tipo_doc.php';
        require_once 'views/layouts/main.php';
    }

    public function listarTiposDocumento()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = $_POST['pagina'] ?? 1;
        $porPagina = $_POST['porPagina'] ?? 10;

        $modelo = new MasterModel();
        $data = $modelo->obtenerTiposDocumento($buscar, $orden, $pagina, $porPagina);
        echo json_encode($data);
    }

    public function crearTipoDocumento()
    {
        $nombre = trim($_POST['nombre_tipo_documento'] ?? '');
        $modelo = new MasterModel();

        if ($modelo->existeTipoDocumento($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El tipo de documento ya existe.']);
            return;
        }

        $ok = $modelo->insertarTipoDocumento($nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de documento agregado correctamente.' : 'Error al guardar el tipo de documento.'
        ]);
    }

    public function editarTipoDocumento()
    {
        $id = $_POST['id_tipo_documento'] ?? null;
        $nombre = trim($_POST['nombre_tipo_documento'] ?? '');
        $modelo = new MasterModel();

        if ($modelo->existeTipoDocumento($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro tipo de documento con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarTipoDocumento($id, $nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de documento actualizado correctamente.' : 'Error al actualizar el registro.'
        ]);
    }

    public function eliminarTipoDocumento()
    {
        $id = $_POST['id_tipo_documento'] ?? null;
        $modelo = new MasterModel();

        if ($modelo->tipoDocumentoEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar, el tipo de documento está en uso.']);
            return;
        }

        $ok = $modelo->eliminarTipoDocumento($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de documento eliminado correctamente.' : 'Error al eliminar el registro.'
        ]);
    }

    /* ===================================================
    SECCIÓN: TIPO DE CONTACTO
    =================================================== */

    // Cargar vista principal del módulo
    public function verFrmTipoCon()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_tipo_contacto.php';
        require_once 'views/layouts/main.php';
    }

    public function listarTiposContacto()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = $_POST['pagina'] ?? 1;
        $porPagina = $_POST['porPagina'] ?? 10;

        $modelo = new MasterModel();
        $data = $modelo->obtenerTiposContacto($buscar, $orden, $pagina, $porPagina);
        echo json_encode($data);
    }

    public function crearTipoContacto()
    {
        $nombre = trim($_POST['nombre_tipo_contacto'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido.']);
            return;
        }

        if ($modelo->existeTipoContacto($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El tipo de contacto ya existe.']);
            return;
        }

        $ok = $modelo->insertarTipoContacto($nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de contacto agregado correctamente.' : 'Error al guardar el tipo de contacto.'
        ]);
    }

    public function editarTipoContacto()
    {
        $id = $_POST['id_tipo_contacto'] ?? null;
        $nombre = trim($_POST['nombre_tipo_contacto'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido.']);
            return;
        }

        if ($modelo->existeTipoContacto($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro tipo de contacto con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarTipoContacto($id, $nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de contacto actualizado correctamente.' : 'Error al actualizar el registro.'
        ]);
    }

    public function eliminarTipoContacto()
    {
        $id = $_POST['id_tipo_contacto'] ?? null;
        $modelo = new MasterModel();

        if ($modelo->tipoContactoEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar, el tipo de contacto está en uso.']);
            return;
        }

        $ok = $modelo->eliminarTipoContacto($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de contacto eliminado correctamente.' : 'Error al eliminar el registro.'
        ]);
    }

    /* ===================================================
    SECCIÓN: GÉNERO
    =================================================== */

    // Cargar vista principal del módulo
    public function verFrmGenero()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_genero.php';
        require_once 'views/layouts/main.php';
    }

    public function listarGeneros()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = $_POST['pagina'] ?? 1;
        $porPagina = $_POST['porPagina'] ?? 10;

        $modelo = new MasterModel();
        $data = $modelo->obtenerGeneros($buscar, $orden, $pagina, $porPagina);
        echo json_encode($data);
    }

    public function crearGenero()
    {
        $nombre = trim($_POST['nombre_genero'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido.']);
            return;
        }

        if ($modelo->existeGenero($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El género ya existe.']);
            return;
        }

        $ok = $modelo->insertarGenero($nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Género agregado correctamente.' : 'Error al guardar el género.'
        ]);
    }

    public function editarGenero()
    {
        $id = $_POST['id_genero'] ?? null;
        $nombre = trim($_POST['nombre_genero'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido.']);
            return;
        }

        if ($modelo->existeGenero($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro género con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarGenero($id, $nombre);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Género actualizado correctamente.' : 'Error al actualizar el registro.'
        ]);
    }

    public function eliminarGenero()
    {
        $id = $_POST['id_genero'] ?? null;
        $modelo = new MasterModel();

        if ($modelo->generoEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar, el género está en uso por una persona.']);
            return;
        }

        $ok = $modelo->eliminarGenero($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Género eliminado correctamente.' : 'Error al eliminar el registro.'
        ]);
    }

    /* ===================================================
    SECCIÓN: PERFIL
    =================================================== */

    public function verFrmPerfiles()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_perfil.php';
        require_once 'views/layouts/main.php';
    }

    /* -----------------------------------------------
    LISTAR PERFILES
    ----------------------------------------------- */
    public function listarPerfiles()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = $_POST['pagina'] ?? 1;
        $porPagina = $_POST['porPagina'] ?? 10;

        $modelo = new MasterModel();
        $data = $modelo->obtenerPerfiles($buscar, $orden, $pagina, $porPagina);
        echo json_encode($data);
    }

    /* -----------------------------------------------
    CREAR PERFIL
    ----------------------------------------------- */
    public function crearPerfil()
    {
        $descripcion = trim($_POST['descripcion_perfil'] ?? '');
        $modelo = new MasterModel();

        if ($descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar una descripción válida.']);
            return;
        }

        if ($modelo->existePerfil($descripcion)) {
            echo json_encode(['success' => false, 'message' => 'El perfil ya existe.']);
            return;
        }

        $ok = $modelo->insertarPerfil($descripcion);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Perfil agregado correctamente.' : 'Error al guardar el perfil.'
        ]);
    }

    /* -----------------------------------------------
    EDITAR PERFIL
    ----------------------------------------------- */
    public function editarPerfil()
    {
        $id = $_POST['id_perfil'] ?? null;
        $descripcion = trim($_POST['descripcion_perfil'] ?? '');
        $modelo = new MasterModel();

        if ($descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar una descripción válida.']);
            return;
        }

        if ($modelo->existePerfil($descripcion, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro perfil con esa descripción.']);
            return;
        }

        $ok = $modelo->actualizarPerfil($id, $descripcion);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar el registro.'
        ]);
    }

    /* -----------------------------------------------
    ELIMINAR PERFIL
    ----------------------------------------------- */
    public function eliminarPerfil()
    {
        $id = $_POST['id_perfil'] ?? null;
        $modelo = new MasterModel();

        if ($modelo->perfilEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar, el perfil está en uso por uno o más módulos.']);
            return;
        }

        $ok = $modelo->eliminarPerfil($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Perfil eliminado correctamente.' : 'Error al eliminar el registro.'
        ]);
    }


    /* ===================================================
    SECCIÓN: MÓDULO
    =================================================== */

    // 📄 Cargar vista principal del módulo
    public function verFrmModulos()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_modulo.php';
        require_once 'views/layouts/main.php';
    }

    public function listarModulos()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = $_POST['pagina'] ?? 1;
        $porPagina = $_POST['porPagina'] ?? 10;

        $modelo = new MasterModel();
        $data = $modelo->obtenerModulos($buscar, $orden, $pagina, $porPagina);
        echo json_encode($data);
    }

    public function crearModulo()
    {
        $descripcion = trim($_POST['descripcion_modulo'] ?? '');
        $modelo = new MasterModel();

        if ($descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar una descripción válida.']);
            return;
        }

        if ($modelo->existeModulo($descripcion)) {
            echo json_encode(['success' => false, 'message' => 'El módulo ya existe.']);
            return;
        }

        $ok = $modelo->insertarModulo($descripcion);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Módulo agregado correctamente.' : 'Error al guardar el módulo.'
        ]);
    }

    public function editarModulo()
    {
        $id = $_POST['id_modulo'] ?? null;
        $descripcion = trim($_POST['descripcion_modulo'] ?? '');
        $modelo = new MasterModel();

        if ($descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar una descripción válida.']);
            return;
        }

        if ($modelo->existeModulo($descripcion, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro módulo con esa descripción.']);
            return;
        }

        $ok = $modelo->actualizarModulo($id, $descripcion);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Módulo actualizado correctamente.' : 'Error al actualizar el registro.'
        ]);
    }

    public function eliminarModulo()
    {
        $id = $_POST['id_modulo'] ?? null;
        $modelo = new MasterModel();

        if ($modelo->moduloEnUso($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar, el módulo está en uso por uno o más perfiles.']);
            return;
        }

        $ok = $modelo->eliminarModulo($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Módulo eliminado correctamente.' : 'Error al eliminar el registro.'
        ]);
    }


    /* ===================================================
    SECCIÓN: ACCESOS
    =================================================== */

    public function verFrmAccesos()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_accesos.php';
        require_once 'views/layouts/main.php';
    }

    /* -----------------------------------------------
    LISTAR ACCESOS (Perfiles, Módulos y Relaciones)
    ----------------------------------------------- */
    public function listarAccesos()
    {
        $modelo = new MasterModel();

        // Se obtienen perfiles y módulos con estructura limpia
        $perfilesData = $modelo->obtenerPerfiles('', 'ASC', 1, 9999);
        $modulosData  = $modelo->obtenerModulos('', 'ASC', 1, 9999);
        $relaciones   = $modelo->obtenerAccesos();

        // Retornar sólo los arrays internos ['datos']
        $data = [
            'perfiles'    => $perfilesData['datos'],
            'modulos'     => $modulosData['datos'],
            'relaciones'  => $relaciones
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /* -----------------------------------------------
    ASIGNAR ACCESO (Toggle ON)
    ----------------------------------------------- */
    public function asignarAcceso()
    {
        $idModulo = $_POST['id_modulo'] ?? null;
        $idPerfil = $_POST['id_perfil'] ?? null;

        if (!$idModulo || !$idPerfil || !is_numeric($idModulo) || !is_numeric($idPerfil)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $modelo = new MasterModel();

        if ($modelo->accesoExiste($idModulo, $idPerfil)) {
            echo json_encode(['success' => false, 'message' => 'El acceso ya está asignado.']);
            return;
        }

        $ok = $modelo->asignarAcceso($idModulo, $idPerfil);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Acceso asignado correctamente.' : 'Error al asignar el acceso.'
        ]);
    }

    /* -----------------------------------------------
    ELIMINAR ACCESO (Toggle OFF)
    ----------------------------------------------- */
    public function eliminarAcceso()
    {
        $idModulo = $_POST['id_modulo'] ?? null;
        $idPerfil = $_POST['id_perfil'] ?? null;

        if (!$idModulo || !$idPerfil || !is_numeric($idModulo) || !is_numeric($idPerfil)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $modelo = new MasterModel();
        $ok = $modelo->eliminarAcceso($idModulo, $idPerfil);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Acceso eliminado correctamente.' : 'Error al eliminar el acceso.'
        ]);
    }

    /* -----------------------------------------------
    TOGGLE ACCESO (Unifica asignar y eliminar)
    ----------------------------------------------- */
    public function toggleAcceso()
    {
        $idModulo = $_POST['id_modulo'] ?? null;
        $idPerfil = $_POST['id_perfil'] ?? null;
        $estado   = $_POST['estado'] ?? null; // true o false

        $modelo = new MasterModel();

        // 🔒 Bloquear modificaciones del perfil "Invitado"
        $nombrePerfil = $modelo->obtenerNombrePerfil($idPerfil);
        if (strtolower($nombrePerfil) === 'invitado') {
            echo json_encode([
                'success' => false,
                'message' => 'El perfil "Invitado" solo tiene permisos de lectura.'
            ]);
            return;
        }

        // Validación de datos
        if (!$idModulo || !$idPerfil || $estado === null) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            return;
        }

        // Si el toggle está activado → asignar
        if ($estado === 'true' || $estado === true) {
            $ok = $modelo->asignarAcceso($idModulo, $idPerfil);
            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Acceso asignado correctamente.' : 'Error al asignar acceso.'
            ]);
        } else {
            // Si está desactivado → eliminar
            $ok = $modelo->eliminarAcceso($idModulo, $idPerfil);
            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Acceso revocado correctamente.' : 'Error al eliminar acceso.'
            ]);
        }
    }

    /* ===================================================
    SECCIÓN: CATEGORÍAS
    =================================================== */
    public function verFrmCategoria()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_categoria.php';
        require_once 'views/layouts/main.php';
    }

    /* ===================================================
    Listar categorías con filtros opcionales
    =================================================== */
    public function listarCategorias()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina = max(1, (int)($_POST['porPagina'] ?? 10));

        // ✅ Solo listar categorías activas (id_estado_logico = 1)
        $todas = $this->model->obtenerCategorias(false);

        // 🔍 Filtro por búsqueda
        if (!empty($buscar)) {
            $todas = array_filter($todas, function ($cat) use ($buscar) {
                return stripos($cat['nombre_categoria'], $buscar) !== false;
            });
        }

        // ↕ Ordenamiento alfabético
        usort($todas, function ($a, $b) use ($orden) {
            return $orden === 'ASC'
                ? strcmp($a['nombre_categoria'], $b['nombre_categoria'])
                : strcmp($b['nombre_categoria'], $a['nombre_categoria']);
        });

        // 📄 Paginación
        $total = count($todas);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todas, $inicio, $porPagina);

        echo json_encode([
            'success' => true,
            'data' => array_values($data),
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    /* ===================================================
    Crear nueva categoría
    =================================================== */
    public function crearCategoria()
    {
        require_once 'views/libs/class.upload/src/class.upload.php';

        $nombre = trim($_POST['nombre_categoria'] ?? '');
        $estado = 1; // Activo por defecto
        $rutaImagen = null;

        // 🧩 Validaciones
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido.']);
            return;
        }

        if (strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            return;
        }

        // 🔎 Verificar duplicado (solo entre categorías activas)
        if ($this->model->categoriaExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre.']);
            return;
        }

        // 🖼️ Procesar imagen si se sube
        if (!empty($_FILES['imagen_categoria']['name'])) {
            $handle = new \Verot\Upload\Upload($_FILES['imagen_categoria']);
            if ($handle->uploaded) {
                $handle->file_new_name_body = 'cat_' . uniqid();
                $handle->image_resize = true;
                $handle->image_x = 800;
                $handle->image_ratio_y = true;
                $handle->allowed = ['image/*'];
                $handle->process('views/public/uploads/categories/images/');

                if ($handle->processed) {
                    $rutaImagen = 'views/public/uploads/categories/images/' . $handle->file_dst_name;
                    $handle->clean();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen: ' . $handle->error]);
                    return;
                }
            }
        }

        // 💾 Guardar en BD
        $ok = $this->model->crearCategoria([
            'nombre_categoria' => $nombre,
            'imagen_categoria' => $rutaImagen,
            'id_estado_logico' => $estado
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Categoría creada correctamente.' : 'Error al guardar la categoría.'
        ]);
    }

    /* ===================================================
    Editar una categoría existente
    =================================================== */
    public function editarCategoria()
    {
        require_once 'views/libs/class.upload/src/class.upload.php';

        $id = intval($_POST['editar_id_categoria'] ?? $_POST['id_categoria'] ?? 0);
        $nombre = trim($_POST['editar_nombre_categoria'] ?? $_POST['nombre_categoria'] ?? '');
        $estado = 1; // Se mantiene activa al editar

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de categoría inválido.']);
            return;
        }

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre de categoría.']);
            return;
        }

        // 🔎 Evita duplicados con otras categorías activas
        if ($this->model->categoriaExiste($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre.']);
            return;
        }

        $categoriaActual = $this->model->obtenerCategoriaPorId($id);
        if (!$categoriaActual) {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada.']);
            return;
        }

        $rutaImagen = $categoriaActual['imagen_categoria'] ?? null;

        // 🖼️ Actualizar imagen si se sube una nueva
        if (!empty($_FILES['editar_imagen_categoria']['name'])) {
            $handle = new \Verot\Upload\Upload($_FILES['editar_imagen_categoria']);
            if ($handle->uploaded) {
                $handle->file_new_name_body = 'cat_' . uniqid();
                $handle->image_resize = true;
                $handle->image_x = 800;
                $handle->image_ratio_y = true;
                $handle->allowed = ['image/*'];
                $handle->process('views/public/uploads/categories/images/');

                if ($handle->processed) {
                    if (!empty($rutaImagen) && file_exists($rutaImagen)) {
                        @unlink($rutaImagen);
                    }
                    $rutaImagen = 'views/public/uploads/categories/images/' . $handle->file_dst_name;
                    $handle->clean();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al subir la nueva imagen: ' . $handle->error]);
                    return;
                }
            }
        }

        $ok = $this->model->actualizarCategoria([
            'id_categoria' => $id,
            'nombre_categoria' => $nombre,
            'imagen_categoria' => $rutaImagen,
            'id_estado_logico' => $estado
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Categoría actualizada correctamente.' : 'Error al actualizar la categoría.'
        ]);
    }

    /* ===================================================
    Eliminar una categoría (baja lógica)
    =================================================== */
    public function eliminarCategoria()
    {
        $id = (int)($_POST['id_categoria'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de categoría no válido.']);
            return;
        }

        $categoria = $this->model->obtenerCategoriaPorId($id);
        if (!$categoria) {
            echo json_encode(['success' => false, 'message' => 'La categoría no existe o ya fue eliminada.']);
            return;
        }

        // 🧩 Baja lógica: cambia estado a 2 (inactivo)
        $ok = $this->model->actualizarCategoria([
            'id_categoria' => $id,
            'nombre_categoria' => $categoria['nombre_categoria'],
            'imagen_categoria' => $categoria['imagen_categoria'],
            'id_estado_logico' => 2
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Categoría eliminada correctamente.' : 'Error al eliminar la categoría.'
        ]);
    }

    /* ===================================================
    SECCIÓN: SUBCATEGORÍAS
    =================================================== */

    public function verFrmSubCategoria()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_sub_categoria.php';
        require_once 'views/layouts/main.php';
    }

    /* ===================================================
    Listar subcategorías (opcionalmente filtradas por categoría)
    =================================================== */
    public function listarSubCategorias()
    {
        $buscar     = $_POST['buscar'] ?? '';
        $orden      = $_POST['orden'] ?? 'ASC';
        $pagina     = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina  = max(1, (int)($_POST['porPagina'] ?? 10));
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);

        // Si viene un ID de categoría, filtramos por ella
        if ($idCategoria > 0) {
            $todas = $this->model->obtenerSubCategoriasPorCategoria($idCategoria);
        } else {
            $todas = $this->model->obtenerSubCategorias(); // Solo activas
        }

        // 🔍 Filtro por búsqueda (nombre)
        if (!empty($buscar)) {
            $todas = array_filter($todas, function ($sub) use ($buscar) {
                return stripos($sub['nombre_sub_categoria'], $buscar) !== false;
            });
        }

        // ↕ Ordenamiento
        usort($todas, function ($a, $b) use ($orden) {
            return $orden === 'ASC'
                ? strcmp($a['nombre_sub_categoria'], $b['nombre_sub_categoria'])
                : strcmp($b['nombre_sub_categoria'], $a['nombre_sub_categoria']);
        });

        // 📄 Paginación
        $total = count($todas);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todas, $inicio, $porPagina);

        echo json_encode([
            'success'   => true,
            'data'      => array_values($data),
            'total'     => $total,
            'pagina'    => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    /* ===================================================
    Crear nueva subcategoría
    =================================================== */
    public function crearSubCategoria()
    {
        $nombre     = trim($_POST['nombre_sub_categoria'] ?? '');
        $cantidad   = (int)($_POST['cant_sub_categoria'] ?? 0);
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $estado     = 1;

        // 🧩 Validaciones
        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido (mínimo 3 caracteres).']);
            return;
        }

        if ($cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a cero.']);
            return;
        }

        if ($idCategoria <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una categoría válida.']);
            return;
        }

        // Evita duplicados dentro de la misma categoría
        if ($this->model->subCategoriaExiste($nombre, $idCategoria)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una subcategoría con ese nombre en esta categoría.']);
            return;
        }

        // Guardar en BD
        $ok = $this->model->crearSubCategoria([
            'nombre_sub_categoria' => $nombre,
            'cant_sub_categoria'   => $cantidad,
            'id_categoria'         => $idCategoria,
            'id_estado_logico'     => $estado
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Subcategoría creada correctamente.' : 'Error al guardar la subcategoría.'
        ]);
    }

    /* ===================================================
    Editar una subcategoría existente
    =================================================== */
    public function editarSubCategoria()
    {
        $id          = (int)($_POST['id_sub_categoria'] ?? 0);
        $nombre      = trim($_POST['nombre_sub_categoria'] ?? '');
        $cantidad    = (int)($_POST['cant_sub_categoria'] ?? 0);
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $estado      = 1;

        // 🧩 Validaciones
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de subcategoría inválido.']);
            return;
        }

        if ($nombre === '' || strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre válido (mínimo 3 caracteres).']);
            return;
        }

        if ($cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a cero.']);
            return;
        }

        if ($idCategoria <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una categoría válida.']);
            return;
        }

        if ($this->model->subCategoriaExiste($nombre, $idCategoria, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una subcategoría con ese nombre en esta categoría.']);
            return;
        }

        $actual = $this->model->obtenerSubCategoriaPorId($id);
        if (!$actual) {
            echo json_encode(['success' => false, 'message' => 'Subcategoría no encontrada.']);
            return;
        }

        // Guardar cambios
        $ok = $this->model->actualizarSubCategoria([
            'id_sub_categoria'      => $id,
            'nombre_sub_categoria'  => $nombre,
            'cant_sub_categoria'    => $cantidad,
            'id_categoria'          => $idCategoria,
            'id_estado_logico'      => $estado
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Subcategoría actualizada correctamente.' : 'Error al actualizar la subcategoría.'
        ]);
    }

    /* ===================================================
    Eliminar una subcategoría (baja lógica)
    =================================================== */
    public function eliminarSubCategoria()
    {
        $id = (int)($_POST['id_sub_categoria'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de subcategoría no válido.']);
            return;
        }

        $existe = $this->model->obtenerSubCategoriaPorId($id);
        if (!$existe) {
            echo json_encode(['success' => false, 'message' => 'La subcategoría no existe o ya fue eliminada.']);
            return;
        }

        // 🧩 Baja lógica (id_estado_logico = 2)
        $ok = $this->model->eliminarSubCategoria($id);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Subcategoría inactivada correctamente.' : 'Error al eliminar la subcategoría.'
        ]);
    }

    /* ===================================================
    Listar categorías activas (para el combo anidado)
    =================================================== */
    public function listarCategoriasActivas()
    {
        $data = $this->model->obtenerCategoriasActivas();

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ===================================================
    SECCIÓN: MARCAS
    =================================================== */

    public function verFrmMarca()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_marca.php';
        require_once 'views/layouts/main.php';
    }

    public function listarMarcas()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina = max(1, (int)($_POST['porPagina'] ?? 10));

        $modelo = new MasterModel();
        $todas = $modelo->obtenerMarcas();

        if (!empty($buscar)) {
            $todas = array_filter(
                $todas,
                fn($m) =>
                stripos($m['nombre_marca'], $buscar) !== false
            );
        }

        usort(
            $todas,
            fn($a, $b) =>
            $orden === 'ASC'
                ? strcmp($a['nombre_marca'], $b['nombre_marca'])
                : strcmp($b['nombre_marca'], $a['nombre_marca'])
        );

        $total = count($todas);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todas, $inicio, $porPagina);

        echo json_encode([
            'success' => true,
            'data' => array_values($data),
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    public function crearMarca()
    {
        $nombre = trim($_POST['nombre_marca'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre.']);
            return;
        }

        if ($modelo->marcaExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'La marca ya existe.']);
            return;
        }

        $ok = $modelo->crearMarca(['nombre_marca' => $nombre]);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Marca creada correctamente.' : 'Error al crear la marca.'
        ]);
    }

    public function editarMarca()
    {
        $id = (int)($_POST['id_marca'] ?? 0);
        $nombre = trim($_POST['nombre_marca'] ?? '');
        $modelo = new MasterModel();

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        if ($modelo->marcaExiste($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una marca con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarMarca([
            'id_marca' => $id,
            'nombre_marca' => $nombre
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Marca actualizada correctamente.' : 'Error al actualizar la marca.'
        ]);
    }

    public function eliminarMarca()
    {
        $id = (int)($_POST['id_marca'] ?? 0);
        $modelo = new MasterModel();

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido.']);
            return;
        }

        $ok = $modelo->eliminarMarca($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Marca eliminada correctamente.' : 'Error al eliminar la marca.'
        ]);
    }

    /* ===================================================
    SECCIÓN: UNIDADES DE MEDIDA
    =================================================== */

    public function verFrmUnidadMedida()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_unidad_medida.php';
        require_once 'views/layouts/main.php';
    }

    public function listarUnidades()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina = max(1, (int)($_POST['porPagina'] ?? 10));

        $modelo = new MasterModel();
        $todas = $modelo->obtenerUnidadesMedida();

        if (!empty($buscar)) {
            $todas = array_filter(
                $todas,
                fn($u) =>
                stripos($u['nombre_unidad_medida'], $buscar) !== false
            );
        }

        usort(
            $todas,
            fn($a, $b) =>
            $orden === 'ASC'
                ? strcmp($a['nombre_unidad_medida'], $b['nombre_unidad_medida'])
                : strcmp($b['nombre_unidad_medida'], $a['nombre_unidad_medida'])
        );

        $total = count($todas);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todas, $inicio, $porPagina);

        echo json_encode([
            'success' => true,
            'data' => array_values($data),
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    public function crearUnidad()
    {
        $nombre = trim($_POST['nombre_unidad_medida'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre.']);
            return;
        }

        if ($modelo->unidadExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'La unidad ya existe.']);
            return;
        }

        $ok = $modelo->crearUnidad(['nombre_unidad_medida' => $nombre]);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Unidad creada correctamente.' : 'Error al crear la unidad.'
        ]);
    }

    public function editarUnidad()
    {
        $id = (int)($_POST['id_unidad_medida'] ?? 0);
        $nombre = trim($_POST['nombre_unidad_medida'] ?? '');
        $modelo = new MasterModel();

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        if ($modelo->unidadExiste($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una unidad con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarUnidad([
            'id_unidad_medida' => $id,
            'nombre_unidad_medida' => $nombre
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Unidad actualizada correctamente.' : 'Error al actualizar la unidad.'
        ]);
    }

    public function eliminarUnidad()
    {
        $id = (int)($_POST['id_unidad_medida'] ?? 0);
        $modelo = new MasterModel();

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido.']);
            return;
        }

        $ok = $modelo->eliminarUnidad($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Unidad eliminada correctamente.' : 'Error al eliminar la unidad.'
        ]);
    }

    /* ===================================================
    SECCIÓN: MÉTODOS DE PAGO
    =================================================== */

    public function verFrmMetodoPago()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_metodo_pago.php';
        require_once 'views/layouts/main.php';
    }

    public function listarMetodosPago()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina = max(1, (int)($_POST['porPagina'] ?? 10));

        $modelo = new MasterModel();
        $todos = $modelo->obtenerMetodosPago();

        if (!empty($buscar)) {
            $todos = array_filter(
                $todos,
                fn($m) =>
                stripos($m['nombre_metodo_pago'], $buscar) !== false
            );
        }

        usort(
            $todos,
            fn($a, $b) =>
            $orden === 'ASC'
                ? strcmp($a['nombre_metodo_pago'], $b['nombre_metodo_pago'])
                : strcmp($b['nombre_metodo_pago'], $a['nombre_metodo_pago'])
        );

        $total = count($todos);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todos, $inicio, $porPagina);

        echo json_encode([
            'success' => true,
            'data' => array_values($data),
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    public function crearMetodoPago()
    {
        $nombre = trim($_POST['nombre_metodo_pago'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre.']);
            return;
        }

        if ($modelo->metodoPagoExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El método de pago ya existe.']);
            return;
        }

        $ok = $modelo->crearMetodoPago(['nombre_metodo_pago' => $nombre]);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Método de pago creado correctamente.' : 'Error al crear el método de pago.'
        ]);
    }

    public function editarMetodoPago()
    {
        $id = (int)($_POST['id_metodo_pago'] ?? 0);
        $nombre = trim($_POST['nombre_metodo_pago'] ?? '');
        $modelo = new MasterModel();

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        if ($modelo->metodoPagoExiste($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un método con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarMetodoPago([
            'id_metodo_pago' => $id,
            'nombre_metodo_pago' => $nombre
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Método de pago actualizado correctamente.' : 'Error al actualizar el método de pago.'
        ]);
    }

    public function eliminarMetodoPago()
    {
        $id = (int)($_POST['id_metodo_pago'] ?? 0);
        $modelo = new MasterModel();

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido.']);
            return;
        }

        $ok = $modelo->eliminarMetodoPago($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Método de pago eliminado correctamente.' : 'Error al eliminar el método de pago.'
        ]);
    }

    /* ===================================================
    SECCIÓN: TIPOS DE NOTA
    =================================================== */

    public function verFrmTipoNota()
    {
        Sesion::iniciar();
        $vista = 'views/masters/frm_tipo_nota.php';
        require_once 'views/layouts/main.php';
    }

    public function listarTiposNota()
    {
        $buscar = $_POST['buscar'] ?? '';
        $orden = $_POST['orden'] ?? 'ASC';
        $pagina = max(1, (int)($_POST['pagina'] ?? 1));
        $porPagina = max(1, (int)($_POST['porPagina'] ?? 10));

        $modelo = new MasterModel();
        $todos = $modelo->obtenerTiposNota();

        if (!empty($buscar)) {
            $todos = array_filter(
                $todos,
                fn($n) =>
                stripos($n['nombre_tipo_nota'], $buscar) !== false
            );
        }

        usort(
            $todos,
            fn($a, $b) =>
            $orden === 'ASC'
                ? strcmp($a['nombre_tipo_nota'], $b['nombre_tipo_nota'])
                : strcmp($b['nombre_tipo_nota'], $a['nombre_tipo_nota'])
        );

        $total = count($todos);
        $inicio = ($pagina - 1) * $porPagina;
        $data = array_slice($todos, $inicio, $porPagina);

        echo json_encode([
            'success' => true,
            'data' => array_values($data),
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    public function crearTipoNota()
    {
        $nombre = trim($_POST['nombre_tipo_nota'] ?? '');
        $modelo = new MasterModel();

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar un nombre.']);
            return;
        }

        if ($modelo->tipoNotaExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El tipo de nota ya existe.']);
            return;
        }

        $ok = $modelo->crearTipoNota(['nombre_tipo_nota' => $nombre]);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de nota creado correctamente.' : 'Error al crear el tipo de nota.'
        ]);
    }

    public function editarTipoNota()
    {
        $id = (int)($_POST['id_tipo_nota'] ?? 0);
        $nombre = trim($_POST['nombre_tipo_nota'] ?? '');
        $modelo = new MasterModel();

        if ($id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        if ($modelo->tipoNotaExiste($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un tipo de nota con ese nombre.']);
            return;
        }

        $ok = $modelo->actualizarTipoNota([
            'id_tipo_nota' => $id,
            'nombre_tipo_nota' => $nombre
        ]);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de nota actualizado correctamente.' : 'Error al actualizar el tipo de nota.'
        ]);
    }

    public function eliminarTipoNota()
    {
        $id = (int)($_POST['id_tipo_nota'] ?? 0);
        $modelo = new MasterModel();

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido.']);
            return;
        }

        $ok = $modelo->eliminarTipoNota($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Tipo de nota eliminado correctamente.' : 'Error al eliminar el tipo de nota.'
        ]);
    }


    /* ===================================================
    Corchete "}" de cierre de la clase MasterController
    =================================================== */
}
