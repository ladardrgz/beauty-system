<?php
require_once 'models/ClienteModel.php';
require_once 'models/HistorialModel.php';
require_once 'models/PedidoModel.php';
require_once 'views/libs/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClienteController
{
    private $clienteModel;
    private $historialModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->historialModel = new HistorialModel();
        Sesion::establecerClienteDesdeBD();
    }

    // Formulario para la creación de clientes
    public function verFrmCliente()
    {
        $generos    = $this->clienteModel->obtenerGeneros();
        $paises     = $this->clienteModel->obtenerPaises();
        $provincias = $this->clienteModel->obtenerProvincias();
        $localidades = $this->clienteModel->obtenerLocalidades();
        $barrios    = $this->clienteModel->obtenerBarrios();
        $tiposDocumento = $this->clienteModel->obtenerTiposDocumento();

        $vista = 'views/client/frm_cliente.php';
        require_once 'views/layouts/main.php';
    }

    // Endpoints de validación (AJAX)

    public function validarEmail()
    {
        $email = $_GET['email'] ?? '';
        $exists = $this->clienteModel->existeEmail($email);
        echo json_encode(['success' => true, 'exists' => $exists]);
    }

    public function validarTelefono()
    {
        $telefono = $_GET['telefono'] ?? '';
        $exists = $this->clienteModel->existeTelefono($telefono);
        echo json_encode(['success' => true, 'exists' => $exists]);
    }

    public function validarUsuario()
    {
        $usuario = $_GET['usuario'] ?? '';
        $exists = $this->clienteModel->existeUsuario($usuario);
        echo json_encode(['success' => true, 'exists' => $exists]);
    }

    // REGISTRO COMPLETO DE CLIENTE
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // Sanitización de datos
        $data = [
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'apellido'         => trim($_POST['apellido'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'genero'           => intval($_POST['genero'] ?? 0),
            'tipo_documento'   => intval($_POST['tipo_documento'] ?? 0),
            'numero_documento' => trim($_POST['numero_documento'] ?? ''),
            'email'            => trim($_POST['email'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? ''),
            'pais'             => intval($_POST['pais'] ?? 0),
            'provincia'        => intval($_POST['provincia'] ?? 0),
            'ciudad'           => intval($_POST['ciudad'] ?? 0),
            'barrio'           => intval($_POST['barrio'] ?? 0),
            'direccion'        => trim($_POST['direccion'] ?? ''),
            'numero'           => trim($_POST['numero'] ?? ''),
            'password'         => $_POST['password'] ?? '',
            'password2'        => $_POST['password2'] ?? '',
            'usuario'          => trim($_POST['usuario'] ?? '')
        ];

        // Validaciones backend
        $errores = [];

        if (empty($data['nombre'])) $errores[] = 'El nombre es obligatorio.';
        if (empty($data['apellido'])) $errores[] = 'El apellido es obligatorio.';

        // Fecha de nacimiento y mayoría de edad
        if (empty($data['fecha_nacimiento'])) {
            $errores[] = 'La fecha de nacimiento es obligatoria.';
        } else {
            $fecha = DateTime::createFromFormat('Y-m-d', $data['fecha_nacimiento']);
            $hoy = new DateTime();
            if (!$fecha || $fecha > $hoy) {
                $errores[] = 'Fecha de nacimiento inválida.';
            } else {
                $edad = $hoy->diff($fecha)->y;
                if ($edad < 18) {
                    $errores[] = 'Debes tener al menos 18 años.';
                }
            }
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido.';
        if (empty($data['telefono'])) $errores[] = 'El teléfono es obligatorio.';
        if (empty($data['genero'])) $errores[] = 'Debe seleccionar un género.';
        if (empty($data['tipo_documento']) || empty($data['numero_documento'])) $errores[] = 'Debe indicar tipo y número de documento.';
        if (empty($data['pais']) || empty($data['provincia']) || empty($data['ciudad']) || empty($data['barrio'])) $errores[] = 'Complete todos los campos de ubicación.';
        if (empty($data['direccion'])) $errores[] = 'La dirección es obligatoria.';
        if ($data['password'] !== $data['password2']) $errores[] = 'Las contraseñas no coinciden.';
        if (strlen($data['password']) < 6) $errores[] = 'La contraseña es demasiado corta.';

        // Duplicados
        if ($this->clienteModel->existeEmail($data['email'])) $errores[] = 'El email ya está registrado.';
        if ($this->clienteModel->existeTelefono($data['telefono'])) $errores[] = 'El teléfono ya está registrado.';
        if ($this->clienteModel->existeUsuario($data['usuario'])) $errores[] = 'El usuario ya existe.';

        if (!empty($errores)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errores)]);
            return;
        }


        // Guardar cliente
        $resultado = $this->clienteModel->insertarCliente($data);

        if (!$resultado['success']) {
            echo json_encode(['success' => false, 'message' => 'Error al registrar cliente.']);
            return;
        }

        // Enviar correo de activación
        $envio = $this->enviarCorreoActivacion($resultado['email'], $resultado['usuario'], $resultado['token']);

        if ($envio) {
            echo json_encode([
                'success' => true,
                'message' => 'Cliente registrado. Se envió un correo para activar la cuenta.'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Cliente registrado, pero no se pudo enviar el correo de activación.'
            ]);
        }
    }

// Envío de correo de activación
private function enviarCorreoActivacion(string $email, string $usuario, string $token): bool
{
    try {
        $mail = new PHPMailer(true);

        // Configuración SMTP desde .env
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $_ENV['MAIL_PORT'];
        $mail->CharSet    = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($email);

        // Asunto
        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta en MizzaStore';

        // Datos seguros para HTML
        $usuarioSeguro = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
        $tokenSeguro   = urlencode($token);

        // URL base desde entorno
        $baseUrl = rtrim($_ENV['APP_URL'], '/');
        $link = "{$baseUrl}/index.php?controller=Activar&action=cuenta&token={$tokenSeguro}";

        // Cuerpo del correo
        $mail->Body = "
            <h2>¡Hola, {$usuarioSeguro}!</h2>
            <p>Gracias por registrarte en <strong>MizzaStore</strong>.</p>
            <p>Para activar tu cuenta, hacé clic en el siguiente enlace:</p>
            <p>
                <a href='{$link}' style='background:#d94b8c;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none;display:inline-block;'>
                    Activar cuenta
                </a>
            </p>
            <p>Si no solicitaste esta cuenta, podés ignorar este mensaje.</p>
        ";

        // Versión texto plano
        $mail->AltBody = "Hola, {$usuario}. Para activar tu cuenta ingresá a este enlace: {$link}";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando correo de activación: " . $e->getMessage());
        return false;
    }
}

// Mostrar la vista del historial de compras
// Utilizado para renderizar la interfaz con filtros, tabla y paginador
public function verSeccionHistorial()
{
    Sesion::iniciar();
    // Solo carga la vista que contiene el frontend del historial
    $vista = 'views/client/historial.php';
    require_once 'views/layouts/main.php';
}


// Endpoint AJAX: retorna historial paginado y filtrado
// Usado por la vista historial.php para cargar datos dinámicos
public function obtenerHistorial()
{
    $idCliente = $_SESSION['id_cliente'] ?? null;

    if (!$idCliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no autenticado']);
        return;
    }

    // Parámetros de paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $inicio = ($pagina - 1) * $registrosPorPagina;

    // Filtros recibidos desde la vista (sin warnings)
    $filtros = [
        'fechaInicio'   => $_GET['fechaInicio']   ?? null,
        'fechaFin'      => $_GET['fechaFin']      ?? null,
        'estadoPedido'  => $_GET['estadoPedido']  ?? null,
        'estadoPago'    => $_GET['estadoPago']    ?? null,
        'metodoPago'    => $_GET['metodoPago']    ?? null,
        'montoMin'      => $_GET['montoMin']      ?? null,
        'montoMax'      => $_GET['montoMax']      ?? null,
        'buscarPedido'  => $_GET['buscarPedido']  ?? null,
    ];

    // Obtener registros filtrados y paginados
    $registros = $this->historialModel->obtenerHistorialFiltrado($idCliente, $filtros, $inicio, $registrosPorPagina);
    $totalRegistros = $this->historialModel->contarHistorialFiltrado($idCliente, $filtros);
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

    echo json_encode([
        'success'        => true,
        'data'           => $registros,
        'paginaActual'   => $pagina,
        'totalPaginas'   => $totalPaginas,
        'totalRegistros' => $totalRegistros
    ]);
}



// Mostrar detalle de un pedido específico del cliente
// Renderiza vista individual con toda la información del pedido
public function verDetallePedido()

{
    if (!isset($_GET['id'])) {
        echo "Pedido no especificado.";
        return;
    }

    $idPedido = (int)$_GET['id'];
    $idCliente = $_SESSION['id_cliente'];

    // El modelo controla la seguridad del pedido (pertenencia al cliente)
    $detalle = $this->historialModel->obtenerDetallePedido($idPedido, $idCliente);

    if (!$detalle) {
        echo "No se encontró el pedido o no pertenece a tu cuenta.";
        return;
    }

    // Datos disponibles en la vista
    $pedido    = $detalle['pedido'];
    $productos = $detalle['productos'];
    $envio     = $detalle['envio'];

    $vista = 'views/client/detalle_pedido.php';
    require_once 'views/layouts/main.php';
}

public function verSeccionClientes()
{
    Sesion::iniciar();

    $vista = 'views/client/gestion_clientes.php'; // Nueva vista que ahora vamos a construir
    require_once 'views/layouts/main.php';
}
// Endpoint AJAX: Obtener clientes filtrados y paginados (ADMIN)
public function obtenerClientes()
{
    Sesion::iniciar();

    // Parámetros de paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $inicio = ($pagina - 1) * $registrosPorPagina;

    // Filtros recibidos desde la vista (sin warnings)
    $filtros = [
        'buscar'            => $_GET['buscar']            ?? null, // nombre, apellido, usuario, documento
        'estado_usuario'    => $_GET['estado_usuario']    ?? null, // 1/0
        'cuenta_activada'   => $_GET['cuenta_activada']   ?? null, // 1/0
        'estado_cliente'    => $_GET['estado_cliente']    ?? null, // 1/2
    ];

    // Obtener registros desde el modelo
    $registros = $this->clienteModel->obtenerClientesFiltrados($filtros, $inicio, $registrosPorPagina);
    $totalRegistros = $this->clienteModel->contarClientesFiltrados($filtros);
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

    echo json_encode([
        'success'        => true,
        'data'           => $registros,
        'paginaActual'   => $pagina,
        'totalPaginas'   => $totalPaginas,
        'totalRegistros' => $totalRegistros
    ]);
}
public function darDeBaja()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $idCliente = $data['id_cliente'] ?? null;

    if (!$idCliente) {
        echo json_encode(['success' => false, 'message' => 'ID cliente requerido']);
        return;
    }

    $resultado = $this->clienteModel->darBajaLogica($idCliente);

    echo json_encode(['success' => $resultado]);
}
public function verSeccionHistorialDesdeAdmin()
{
    Sesion::iniciar();

    // Validar ID recibido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "ID de cliente no proporcionado o inválido.";
        return;
    }

    $idCliente = intval($_GET['id']);

    // 🔹 Datos del cliente (se mantiene igual)
    $clienteInfo = $this->clienteModel->obtenerDatosBasicosCliente($idCliente);

    // 🔹 Obtener historial reutilizando HistorialModel (sin perder impresión de datos)
    $historial = $this->historialModel->obtenerHistorialFiltradoAdmin(
        $idCliente,
        [],     // Sin filtros iniciales
        0,      // Desde el inicio
        50      // Cantidad inicial (puedes ajustar o quitar si hay paginación AJAX)
    );

    // 🔹 Enviar a la vista
    $datos = [
        'cliente'  => $clienteInfo,
        'historial'=> $historial,
    ];

    // Cargar vista
    $vista = 'views/client/historial_compras.php';
    require_once 'views/layouts/main.php';
}
// ===============================================
// AJAX: Obtener historial de compras (ADMIN)
// ===============================================
public function obtenerHistorialComprasAdmin()
{
    Sesion::iniciar();
    header('Content-Type: application/json; charset=utf-8');

    // Validar ID recibido
    if (!isset($_GET['idCliente']) || !is_numeric($_GET['idCliente'])) {
        echo json_encode(['success' => false, 'message' => 'ID de cliente no válido']);
        return;
    }

    $idCliente = intval($_GET['idCliente']);

    // Paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
    $inicio = ($pagina - 1) * $registrosPorPagina;

    // Filtros dinámicos
    $filtros = [
        'buscarPedido' => $_GET['buscar'] ?? null,
        'estadoPedido' => $_GET['estado_pedido'] ?? null,
        'estadoPago'   => $_GET['estado_pago'] ?? null,
    ];

    // 🔹 Importante: usar HistorialModel si lo tienes separado
    $historialModel = new HistorialModel();

    // Obtener registros filtrados y paginados
    $registros = $historialModel->obtenerHistorialFiltrado($idCliente, $filtros, $inicio, $registrosPorPagina);
    $totalRegistros = $historialModel->contarHistorialFiltrado($idCliente, $filtros);
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

    echo json_encode([
        'success'        => true,
        'data'           => $registros,
        'paginaActual'   => $pagina,
        'totalPaginas'   => $totalPaginas,
        'totalRegistros' => $totalRegistros
    ]);
}




# Exportar listado de clientes a Excel (.xlsx)
public function exportarListadoClientes()
{
    try {
        $model = new ClienteModel();
        $clientes = $model->obtenerClientesParaExportar();

        if (empty($clientes)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'message' => 'No hay clientes disponibles para exportar.'
            ]);
            return;
        }

        // Crear el libro de Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Listado de Clientes');

        // Encabezados
        $headers = [
            'A1' => 'Cliente #',
            'B1' => 'Nombre completo',
            'C1' => 'Documento',
            'D1' => 'Correo electrónico',
            'E1' => 'Teléfono',
            'F1' => 'Usuario',
            'G1' => 'Fecha que se registró',
            'H1' => 'Estado del cliente'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Estilo del encabezado (igual al que usás)
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DDDDDD']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'AAAAAA']
                ]
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

$row = 2;
foreach ($clientes as $c) {
    $sheet->setCellValue("A{$row}", $c['id_cliente']);
    $sheet->setCellValue("B{$row}", $c['nombre_completo']);
    $sheet->setCellValue("C{$row}", $c['documento']);
    $sheet->setCellValue("D{$row}", $c['email']);
    $sheet->setCellValue("E{$row}", $c['telefono']);
    $sheet->setCellValue("F{$row}", $c['usuario']);
    $sheet->setCellValue("G{$row}", $c['fecha_registro']);

    // 👉 Aquí se traduce el estado numérico a texto
    $estadoTexto = ($c['estado_cliente'] == 1) ? 'Registrado' : 'Eliminado';
    $sheet->setCellValue("H{$row}", $estadoTexto);

    $row++;
}

        // Ajustar ancho de columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordes para todos los datos
        $sheet->getStyle("A1:H" . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Nombre del archivo
        date_default_timezone_set('America/Argentina/San_Luis');
        $nombreArchivo = 'listado_clientes_' . date('d_m_Y') . '.xlsx';

        // Encabezados HTTP
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        // Exportar
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

    } catch (Throwable $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'message' => 'Error al generar el Excel.',
            'error' => $e->getMessage()
        ]);
    }
}





} // <- Fin del controller
