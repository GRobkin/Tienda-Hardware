<?php
/**
 * Layout admin — Ferretería Hardware
 * FIXES: rutas CSS correctas, UIkit CSS cargado, session_start centralizado
 */
if(session_status() === PHP_SESSION_NONE) session_start();

$ruta_actual = $_SERVER['PATH_INFO'] ?? '/';
function admin_activo($prefijo) {
    global $ruta_actual;
    return str_starts_with($ruta_actual, $prefijo) ? 'uk-active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo s($titulo ?? 'Admin'); ?> — Panel Admin</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/estilo.css">
</head>
<body class="panel-admin">

<!-- Nav admin -->
<nav class="panel-admin__nav uk-navbar-container" uk-navbar>
    <div class="uk-navbar-left">
        <a href="/admin/dashboard" class="uk-navbar-item uk-logo panel-admin__logo">
            ⚙ Admin
        </a>
        <ul class="uk-navbar-nav uk-visible@m">
            <li class="<?php echo admin_activo('/admin/dashboard'); ?>">
                <a href="/admin/dashboard">Dashboard</a>
            </li>
            <li class="<?php echo admin_activo('/admin/productos'); ?>">
                <a href="/admin/productos">Productos</a>
            </li>
            <li class="<?php echo admin_activo('/admin/categorias'); ?> <?php echo admin_activo('/admin/subcategorias'); ?>">
                <a href="#">Catálogo <span uk-icon="icon: chevron-down; ratio:.7"></span></a>
                <div class="uk-navbar-dropdown">
                    <ul class="uk-nav uk-navbar-dropdown-nav">
                        <li class="<?php echo admin_activo('/admin/categorias'); ?>">
                            <a href="/admin/categorias">Categorías</a>
                        </li>
                        <li class="<?php echo admin_activo('/admin/subcategorias'); ?>">
                            <a href="/admin/subcategorias">Subcategorías</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="<?php echo admin_activo('/admin/ordenes'); ?>">
                <a href="/admin/ordenes">Órdenes</a>
            </li>
            <li class="<?php echo admin_activo('/admin/usuarios'); ?>">
                <a href="/admin/usuarios">Usuarios</a>
            </li>
        </ul>
    </div>
    <div class="uk-navbar-right uk-margin-right">
        <span class="panel-admin__usuario uk-visible@s">
            <?php echo s($_SESSION['nombre'] ?? ''); ?> <?php echo s($_SESSION['apellido'] ?? ''); ?>
        </span>
        <a href="/" class="uk-button uk-button-small uk-button-default uk-margin-small-left" target="_blank">
            Ver tienda
        </a>
        <form method="POST" action="/logout" class="uk-display-inline uk-margin-small-left">
            <button type="submit" class="uk-button uk-button-small uk-button-danger">Salir</button>
        </form>
    </div>
</nav>

<!-- Contenido -->
<div class="panel-admin__contenido uk-container uk-margin-medium-top">
    <div class="panel-admin__cabecera uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h1 class="panel-admin__titulo"><?php echo s($titulo ?? ''); ?></h1>
    </div>
    <?php echo $contenido; ?>
</div>

<script src="/js/app.js"></script>
</body>
</html>
