<?php
require_once 'models/VentaModel.php';

class VentaController
{
    private $ventaModel;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
    }

    // Vista principal
    public function verSeccionVenta()
    {
        Sesion::iniciar();
        $vista = 'views/ventas/ventas.php';
        require_once 'views/layouts/main.php';
    }

    // Listar pedidos con filtros y paginación
    public function listar()
    {
        $documento = $_GET['documento'] ?? '';
        $contacto  = $_GET['contacto']  ?? '';
        $pedido    = $_GET['pedido']    ?? '';

        // Paginación segura
        $pagina    = max(1, intval($_GET['pagina']    ?? 1));
        $registros = max(1, intval($_GET['registros'] ?? 10));
        $offset    = ($pagina - 1) * $registros;

        // Consultas al modelo
        $pedidos        = $this->ventaModel->obtenerPedidosPaginado($documento, $contacto, $pedido, $registros, $offset);
        $totalRegistros = $this->ventaModel->contarPedidos($documento, $contacto, $pedido);
        $totalPaginas   = ceil($totalRegistros / $registros);

        // Respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'pedidos'         => $pedidos,
            'pagina'          => $pagina,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros
        ]);
    }

    // Detalle de un pedido
    public function detalle()
    {
        $idPedido = intval($_GET['id_pedido'] ?? 0);

        if ($idPedido <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'id_pedido inválido']);
            return;
        }

        $data = $this->ventaModel->obtenerDetallePedido($idPedido);

        header('Content-Type: application/json');
        echo json_encode($data ?: ['error' => 'Pedido no encontrado']);
    }
public function exportarExcel()
{
    // Filtros opcionales
    $documento = $_GET['documento'] ?? '';
    $contacto  = $_GET['contacto']  ?? '';
    $pedido    = $_GET['pedido']    ?? '';

    // Traigo todos los datos (sin límite real)
    $pedidos = $this->ventaModel->obtenerPedidosPaginado(
        $documento,
        $contacto,
        $pedido,
        100000, // límite grande
        0
    );

    // 🔥 Encabezados con codificación UTF-8 y BOM para Excel
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=pedidos_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Enviar BOM UTF-8
    echo "\xEF\xBB\xBF";

    // Genero tabla
    echo "<table border='1'>";
    echo "<tr>
            <th>Pedido #</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Documento</th>
            <th>Contacto</th>
            <th>Monto total</th>
          </tr>";

    foreach ($pedidos as $p) {
        echo "<tr>
                <td>{$p['id_pedido']}</td>
                <td>{$p['fecha_pedido']}</td>
                <td>{$p['nombre_persona']} {$p['apellido_persona']}</td>
                <td>" . ($p['documento'] ?? '-') . "</td>
                <td>" . ($p['contacto'] ?? '-') . "</td>
                <td>" . number_format($p['monto_total'], 2, ',', '.') . "</td>
              </tr>";
    }

    echo "</table>";
}


}
