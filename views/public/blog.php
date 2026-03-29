<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    .articulos-blog {
        padding: 20px 0;
        background: #fff7f8;
        text-align: center;
    }

    .articulos-blog .title {
        font-size: 2.4rem;
        font-weight: 600;
        color: #2C0703;
        margin-bottom: 50px;
        position: relative;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 1.2px;
    }

    .articulos-blog .title::after {
        content: "";
        display: block;
        width: 90px;
        height: 3px;
        background: linear-gradient(90deg, #B6465F, #EBA1A6);
        margin: 10px auto 0;
        border-radius: 2px;
    }

    .articulos-blog .col-3 {
        background: #fff;
        border-radius: 18px;
        padding: 40px 25px 55px;
        margin: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        text-align: center;
        cursor: pointer;
        max-width: 320px;
        position: relative;
    }

    .articulos-blog .col-3:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 30px rgba(182, 70, 95, 0.25);
    }

    .fa.fa-quote-left {
        font-size: 34px;
        color: #B6465F;
        margin-bottom: 15px;
        display: block;
    }

    .articulos-blog .col-3 p {
        font-size: 14px;
        color: #555;
        margin: 12px 0 20px;
        line-height: 1.6;
        min-height: 80px;
    }

    .articulos-blog .col-3 img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        border: 3px solid #EBD4CB;
        box-shadow: 0 0 0 3px rgba(182, 70, 95, 0.15);
    }

    .articulos-blog .col-3 h3 {
        font-weight: 600;
        color: #2C0703;
        font-size: 16px;
        margin-top: 5px;
    }

    .articulos-blog .col-3 i {
        font-style: normal;
        font-weight: 600;
        color: #B6465F;
        display: block;
        margin-bottom: 10px;
        font-size: 15px;
    }

    .btn-comentar {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(90deg, #B6465F, #EBA1A6);
        color: #fff;
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all .3s ease;
    }

    .btn-comentar:hover {
        transform: translateX(-50%) translateY(-3px);
        box-shadow: 0 8px 18px rgba(182, 70, 95, 0.25);
        opacity: .9;
    }

    @media (max-width: 768px) {
        .articulos-blog .col-3 {
            width: 90%;
            margin: 10px auto;
        }
    }
</style>

<div class="articulos-blog">
    <div class="small-container">
        <h2 class="title">¡Bienvenidx al blog de Beauty!</h2>

        <div class="row justify-content-center">

            <?php if (!empty($articulos)) : ?>
                <?php foreach ($articulos as $item): ?>
                    <div class="col-3 card-post"
                        onclick="window.location='index.php?controller=Blog&action=verPublico&id=<?= $item['id_post'] ?>'">

                        <!-- Ícono decorativo -->
                        <i class="fa fa-quote-left"></i>

                        <!-- Título del post -->
                        <h3 class="titulo-post">
                            <?= htmlspecialchars(substr($item['titulo'], 0, 60)) ?>
                            <?= strlen($item['titulo']) > 60 ? '...' : '' ?>
                        </h3>

                        <!-- Resumen -->
                        <p class="preview">
                            <?= htmlspecialchars(substr(strip_tags($item['contenido']), 0, 120)) ?>...
                        </p>

                        <!-- Imagen de autor -->
                        <img src="assets/images/perfil.png" alt="Autor del artículo">

                        <!-- Autor -->
                        <h4 class="autor"><?= htmlspecialchars($item['autor']) ?></h4>

                        <!-- Botón comentar -->
                        <a href="index.php?controller=Blog&action=verPublico&id=<?= $item['id_post'] ?>"
                            class="btn-comentar"
                            onclick="event.stopPropagation();">
                            <i class="fa fa-comment"></i> Comentar
                        </a>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay publicaciones disponibles.</p>
            <?php endif; ?>

        </div>
    </div>
</div>