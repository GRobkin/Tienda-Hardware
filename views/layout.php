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

   <?php include __DIR__ . '/parciales/footer.php'; ?>

    <script src="/js/app.js"></script>
</body>
</html>