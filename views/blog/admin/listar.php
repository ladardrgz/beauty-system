<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de publicaciones</title>

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bordo: #7a1c4b;
            --rosa: #d94b8c;
            --rosaclaro: #f9e2ec;
            --texto: #2b1a1f;
        }
        .table thead th { background-color: var(--bordo); color: #fff; }
        .table tbody tr:hover { background-color: var(--rosaclaro); }

        .btn-create {
            background: linear-gradient(135deg, var(--rosa), var(--bordo));
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
        }

        .icon-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }
        .icon-btn:hover { transform: scale(1.2); color: var(--bordo); }
        .modal-header { background-color: var(--bordo); color: white; }
    </style>
</head>

<body>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa fa-edit me-2"></i>Gestión de publicaciones del blog</h2>

        <button class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="fa fa-plus"></i> Nueva publicación
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($articulos)) : ?>
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articulos as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['titulo']) ?></td>
                                <td><?= htmlspecialchars($item['autor']) ?></td>
                                <td><?= date("d/m/Y H:i", strtotime($item['fecha_publicacion'])) ?></td>
                                <td class="text-center">
                                    <button class="icon-btn" data-bs-toggle="modal"
                                        data-bs-target="#modalEditar<?= $item['id_post'] ?>">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <button class="icon-btn btn-delete"
                                            data-id="<?= $item['id_post'] ?>"
                                            data-titulo="<?= htmlspecialchars($item['titulo']) ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-secondary text-center">No hay publicaciones registradas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL CREAR -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?controller=Blog&action=guardar" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Título:</label>
                    <input type="text" name="titulo" class="form-control mb-3" required>

                    <label>Contenido:</label>
                    <textarea name="contenido" class="form-control" rows="6" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Publicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODALES EDITAR -->
<?php foreach ($articulos as $item): ?>
<div class="modal fade" id="modalEditar<?= $item['id_post'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?controller=Blog&action=actualizar" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Editar: <?= htmlspecialchars($item['titulo']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_post" value="<?= $item['id_post'] ?>">
                    <label>Título:</label>
                    <input type="text" name="titulo" class="form-control mb-3"
                        value="<?= htmlspecialchars($item['titulo']) ?>" required>

                    <label>Contenido:</label>
                    <textarea name="contenido" class="form-control" rows="6" required><?= htmlspecialchars($item['contenido']) ?></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
// Confirmación de eliminación
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const titulo = this.getAttribute('data-titulo');

        Swal.fire({
            title: "¿Eliminar publicación?",
            html: `Se eliminará <strong>${titulo}</strong> permanentemente.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = `index.php?controller=Blog&action=eliminar&id=${id}`;
            }
        });
    });
});
</script>

<?php if (!empty($_SESSION['mensaje'])): ?>
<script>
Swal.fire({
    icon: '<?= $_SESSION['mensaje']['tipo']; ?>',
    title: '<?= $_SESSION['mensaje']['texto']; ?>',
    timer: 2000,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['mensaje']); endif; ?>

</body>
</html>
