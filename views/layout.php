<?php
// Plantilla base: envuelve todo el contenido con nav, footer y scripts comunes
if (session_status() === PHP_SESSION_NONE) session_start();

$ruta = $_SERVER['PATH_INFO'] ?? '/';

$rutas_auth = ['/login', '/registro', '/mensaje'];
$es_auth    = in_array($ruta, $rutas_auth);
$es_admin   = str_starts_with($ruta, '/admin');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= s($meta_descripcion ?? 'Tienda Hardware — componentes, periféricos y tecnología') ?>">
    <meta name="csrf" content="<?= csrf_token() ?>">
    <title><?= s($titulo ?? 'Tienda Hardware') ?> | <?= $es_admin ? 'Admin' : 'Tienda' ?></title>
    <link rel="stylesheet" href="/css/estilo.css?v=<?= filemtime(__DIR__ . '/../public/css/estilo.css') ?>">
</head>
<body>

    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>

    <?php if ($es_auth): ?>
        <?php include __DIR__ . '/parciales/nav-auth.php'; ?>
    <?php else: ?>
        <?php include __DIR__ . '/parciales/nav.php'; ?>
    <?php endif; ?>

    <?php if ($es_admin): ?>
        <?php include __DIR__ . '/parciales/admin-tabs.php'; ?>
    <?php endif; ?>

    <main id="contenido-principal" class="<?= $es_admin ? 'main--admin' : '' ?>">
        <?= $contenido ?>
    </main>

    <?php if (!$es_admin): ?>
        <?php include __DIR__ . '/parciales/footer.php'; ?>
    <?php endif; ?>

    <script src="/js/vendor/sweetalert2.all.min.js"></script>
    <script src="/js/app.js?v=<?= filemtime(__DIR__ . '/../public/js/app.js') ?>"></script>
</body>
</html>
