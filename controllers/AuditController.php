<?php
class AuditController
{
    /**
     * Sección - Auditoría
     * Muestra el panel principal de auditoría del sistema.
     */
    public function verSeccionAudit()
    {
        Sesion::iniciar();
        $vista = 'views/audit/auditoria.php';
        require_once 'views/layouts/main.php';
    }



    public function listar()
{
    $modelo = new AuditModel();

    $pagina    = $_GET['pagina']    ?? 1;
    $registros = $_GET['registros'] ?? 10;

    $filtros = [
        'usuario' => $_GET['usuario'] ?? '',
        'accion'  => $_GET['accion']  ?? '',
        'tabla'   => $_GET['tabla']   ?? '',
        'desde'   => $_GET['desde']   ?? '',
        'hasta'   => $_GET['hasta']   ?? ''
    ];

    $data = $modelo->listar($pagina, $registros, $filtros);
    $total = $modelo->contar($filtros);
    $totalPaginas = ceil($total / $registros);

    echo json_encode([
        'estado' => true,
        'registros' => $data,
        'total_paginas' => $totalPaginas
    ]);
}

public function estadisticas()
{
    $model = new AuditModel();
    $data = $model->obtenerEstadisticas();

    echo json_encode([
        'estado' => true,
        'data' => $data
    ]);
}


}
