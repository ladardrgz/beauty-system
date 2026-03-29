<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['titulo']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

   <style>
    :root {
        --bordo: #7a1c4b;
        --bordo-oscuro: #561133;
        --rosa-claro: #f9e2ec;
        --gris-claro: #f3f3f3;
        --gris-borde: #ddd;
        --texto: #2b1a1f;
        --shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    body {
        background: #fafafa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--texto);
    }

    h2, h3, h4, h5 {
        color: var(--bordo);
        font-weight: 600;
    }

    .post-contenido {
        white-space: pre-line;
        font-size: 1.15rem;
        line-height: 1.8;
        padding: 15px;
        background: white;
        border-radius: 6px;
        box-shadow: var(--shadow);
    }

    /* Comentarios */
    .comentario {
        background: #ffffff;
        border: 1px solid var(--gris-borde);
        border-left: 6px solid var(--bordo);
        padding: 12px 15px;
        border-radius: 5px;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .comentario strong {
        color: var(--bordo);
        font-size: 1.05rem;
    }

    .comentario span {
        display: block;
        margin: 5px 0 3px;
    }

    .comentario small {
        color: #666;
    }

    /* Botón comentar */
    .btn-comentar {
        background: linear-gradient(135deg, var(--bordo), var(--bordo-oscuro));
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        transition: all 0.2s ease-in-out;
    }

    .btn-comentar:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        color: white;
    }

    /* Botón volver */
    .btn-secondary {
        border-radius: 6px;
        padding: 10px 18px;
    }

    /* Formulario de comentario */
    textarea {
        border: 1px solid var(--gris-borde);
        border-radius: 6px;
        transition: 0.3s;
    }

    textarea:focus {
        outline: none;
        border-color: var(--bordo);
        box-shadow: 0 0 6px rgba(122,28,75,0.4);
    }

    /* Contenedor principal */
    .container {
        max-width: 800px;
        background: #ffffff;
        padding: 35px;
        border-radius: 8px;
        box-shadow: var(--shadow);
        margin-bottom: 40px;
    }

    hr {
        border-top: 1px solid var(--gris-borde);
        margin: 25px 0;
    }
</style>

</head>

<body>
    <div class="container mt-5">

        <!-- Título -->
        <h2 class="mb-1"><?= htmlspecialchars($post['titulo']) ?></h2>

        <!-- Autor y fecha -->
        <p class="text-muted">
            Publicado por <?= htmlspecialchars($post['autor']) ?> |
            <?= date("d/m/Y H:i", strtotime($post['fecha_publicacion'])) ?>
        </p>

        <hr>

        <!-- Contenido -->
        <div class="post-contenido mb-5">
            <?= nl2br(htmlspecialchars($post['contenido'])) ?>
        </div>

        <hr>

        <!-- Comentarios -->
        <h4 class="mt-4">
            Comentarios (<?= count($comentarios) ?>)
        </h4>

        <?php if ($comentarios): ?>
            <?php foreach ($comentarios as $coment): ?>
                <div class="comentario p-3 mb-3 rounded">
                    <strong><?= htmlspecialchars($coment['cliente']) ?></strong><br>
                    <span><?= nl2br(htmlspecialchars($coment['comentario'])) ?></span><br>
                    <small class="text-muted">
                        <?= date("d/m/Y H:i", strtotime($coment['fecha'])) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Aún no hay comentarios. Sé el primero.</p>
        <?php endif; ?>

        <hr>

        <!-- Formulario para comentar -->
        <h5 class="mt-4">Dejá tu comentario</h5>

        <form action="index.php?controller=Blog&action=guardarComentarioPublico" method="POST">

            <!-- ID del post -->
            <input type="hidden" name="id_post" value="<?= $post['id_post'] ?>">

            <!-- ID cliente (true ID si está logueado, o default) -->
            <input type="hidden" name="id_cliente"
                value="<?= $_SESSION['id_cliente'] ?? 1 ?>">

            <!-- Área comentario -->
            <textarea name="comentario"
                class="form-control mb-3"
                rows="4"
                placeholder="Escribe tu comentario..."
                required></textarea>

            <!-- Botón enviar -->
            <button type="submit" class="btn btn-comentar">
                <i class="fa fa-paper-plane"></i> Enviar comentario
            </button>

            <!-- Botón volver -->
            <a href="index.php?controller=Home&action=blog" class="btn btn-secondary ms-2">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>