<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$ruta = $_SERVER['PATH_INFO'] ?? '/';

$rutas_auth = ['/login', '/registro', '/olvide', '/restablecer', '/mensaje', '/confirmar'];
$es_auth    = in_array($ruta, $rutas_auth);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= s($meta_descripcion ?? 'Tienda Hardware — componentes, periféricos y tecnología') ?>">
    <title><?= s($titulo ?? 'Tienda Hardware') ?> | Tienda</title>
    <link rel="stylesheet" href="/css/estilo.css">
</head>
<body>

    <?php if ($es_auth): ?>
        <?php include __DIR__ . '/parciales/nav-auth.php'; ?>
    <?php else: ?>
        <?php include __DIR__ . '/parciales/nav.php'; ?>
    <?php endif; ?>

    <main id="contenido-principal">
        <?= $contenido ?>
    </main>

    <?php if (!$es_auth): ?>
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__grid">

                <div class="footer__col">
                    <a href="/" class="footer__logo">
                        <img src="/img/logo.webp" alt="Tienda Hardware">
                    </a>
                    <p class="footer__desc">
                        Tu tienda de hardware, componentes y tecnología de confianza.
                    </p>
                </div>

                <div class="footer__col">
                    <h4 class="footer__titulo">Categorías</h4>
                    <ul class="footer__lista">
                        <?php
                        if (empty($categorias_nav)) {
                            try { $categorias_nav = \Model\Categoria::all('ASC'); }
                            catch (\Throwable $e) { $categorias_nav = []; }
                        }
                        foreach (array_slice($categorias_nav, 0, 6) as $cat):
                        ?>
                        <li>
                            <a href="/categoria-producto/categoria?categoria=<?= s($cat->slug) ?>">
                                <?= s($cat->nombre) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer__col">
                    <h4 class="footer__titulo">Mi cuenta</h4>
                    <ul class="footer__lista">
                        <?php if (is_auth()): ?>
                        <li><a href="/mis-pedidos">Mis pedidos</a></li>
                        <li><a href="/carrito">Carrito</a></li>
                        <?php else: ?>
                        <li><a href="/login">Iniciar sesión</a></li>
                        <li><a href="/registro">Crear cuenta</a></li>
                        <?php endif; ?>
                        <li><a href="/sobre">Sobre nosotros</a></li>
                        <li><a href="/contacto">Contacto</a></li>
                    </ul>
                </div>

            </div>

            <div class="footer__copy">
                <p>&copy; <?= date('Y') ?> Tienda Hardware. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <script src="/js/app.js"></script>
</body>
</html>