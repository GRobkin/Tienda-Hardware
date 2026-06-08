<?php
/**
 * Layout principal — Ferretería Hardware
 * session_start ya fue llamado en funciones.php (cargado via app.php)
 * $categorias_nav se pasa desde los controllers o se carga aquí como fallback
 */
if(empty($categorias_nav)) {
    try {
        $categorias_nav = \Model\Categoria::all('ASC');
    } catch(\Throwable $e) {
        $categorias_nav = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo s($meta_descripcion ?? 'Ferretería Hardware — componentes, periféricos y tecnología'); ?>">
    <title><?php echo s($titulo ?? 'Ferretería Hardware'); ?> | Ferretería</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/estilo.css">
</head>
<body>

<!-- ── Offcanvas mobile ─────────────────────────────────────── -->
<div id="menu-mobile" uk-offcanvas="overlay: true">
    <div class="uk-offcanvas-bar menu-mobile__barra">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <a href="/" class="menu-mobile__logo">⚙ Ferretería</a>
        <p class="menu-mobile__subtitulo">Categorías</p>
        <ul class="uk-nav uk-nav-default">
            <?php foreach($categorias_nav as $cat): ?>
            <li>
                <a href="/categoria-producto/categoria?categoria=<?php echo s($cat->slug); ?>">
                    <?php echo s($cat->nombre); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <p class="menu-mobile__subtitulo">Cuenta</p>
        <ul class="uk-nav uk-nav-default">
            <?php if(is_auth()): ?>
            <li><a href="/mis-pedidos">Mis pedidos</a></li>
            <li><a href="/carrito">Carrito</a></li>
            <li>
                <form method="POST" action="/logout">
                    <button type="submit" class="uk-button uk-button-small uk-button-danger uk-margin-small-top">
                        Cerrar sesión
                    </button>
                </form>
            </li>
            <?php else: ?>
            <li><a href="/login">Iniciar sesión</a></li>
            <li><a href="/registro">Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- ── Barra superior ──────────────────────────────────────── -->
<div class="barra-superior">
    <div class="uk-container">
        <div class="uk-flex uk-flex-between uk-flex-middle">
            <ul class="menu-superior">
                <li><a href="/sobre">Nosotros</a></li>
                <li><a href="/contacto">Contacto</a></li>
            </ul>
            <div class="uk-flex uk-flex-middle" style="gap:12px">
                <?php if(is_auth()): ?>
                <span class="barra-superior__bienvenida">
                    Hola, <strong><?php echo s($_SESSION['nombre']); ?></strong>
                </span>
                <form method="POST" action="/logout" style="margin:0">
                    <button type="submit" class="barra-superior__enlace">Cerrar sesión</button>
                </form>
                <?php else: ?>
                <a href="/login" class="barra-superior__enlace">Iniciar sesión</a>
                <a href="/registro" class="barra-superior__enlace barra-superior__enlace--resaltado">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Header ──────────────────────────────────────────────── -->
<div uk-sticky="sel-target: .encabezado; cls-active: encabezado--fija; top: 36; animation: uk-animation-slide-top-small">
<header class="encabezado">
    <div class="encabezado__fila-principal">
        <div class="uk-container uk-flex uk-flex-middle" style="height:64px; gap:16px">

            <button class="encabezado__hamburger uk-hidden@m" type="button"
                    uk-toggle="target: #menu-mobile" aria-label="Abrir menú">
                <span uk-icon="menu"></span>
            </button>

            <a href="/" class="encabezado__logo">⚙ Ferretería</a>

            <div class="encabezado__buscador uk-flex-1" style="max-width:480px; position:relative">
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon" uk-icon="search"></span>
                    <input id="buscador-input" class="uk-input buscador__campo"
                           type="search" placeholder="Buscar productos..."
                           autocomplete="off" aria-label="Buscar productos">
                </div>
                <div id="buscador-resultados" class="buscador__resultados" style="display:none"></div>
            </div>

            <div class="encabezado__acciones uk-flex uk-flex-middle" style="gap:4px; margin-left:auto">
                <?php if(is_auth()): ?>
                <a href="/mis-pedidos" class="encabezado__boton-icono"
                   uk-tooltip="title: Mis pedidos; pos: bottom">
                    <span uk-icon="icon: list; ratio: 1.1"></span>
                </a>
                <?php endif; ?>
                <a href="/carrito" class="encabezado__boton-icono"
                   uk-tooltip="title: Carrito; pos: bottom">
                    <span uk-icon="icon: cart; ratio: 1.1"></span>
                    <?php $cant = total_carrito(); ?>
                    <span class="encabezado__contador-carrito" id="contador-carrito"
                          style="<?php echo $cant > 0 ? '' : 'display:none'; ?>">
                        <?php echo $cant; ?>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <?php if(!empty($categorias_nav)): ?>
    <nav class="encabezado__nav-categorias" aria-label="Categorías">
        <div class="uk-container">
            <ul class="nav-categorias__lista">
                <?php foreach($categorias_nav as $cat): ?>
                <li class="nav-categorias__item">
                    <a href="/categoria-producto/categoria?categoria=<?php echo s($cat->slug); ?>"
                       class="nav-categorias__enlace <?php echo pagina_activa('/categoria-producto'); ?>">
                        <?php echo s($cat->nombre); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
</header>
</div>

<!-- ── Contenido ───────────────────────────────────────────── -->
<main id="contenido-principal">
    <?php echo $contenido; ?>
</main>

<!-- ── Pie ─────────────────────────────────────────────────── -->
<footer class="pie-pagina">
    <div class="uk-container">
        <div class="uk-grid uk-grid-large uk-child-width-1-1 uk-child-width-1-3@m" uk-grid>
            <div>
                <a href="/" class="pie-pagina__logo">⚙ Ferretería</a>
                <p class="pie-pagina__descripcion">
                    Tu tienda de hardware, componentes y tecnología de confianza.
                </p>
            </div>
            <div>
                <h4 class="pie-pagina__subtitulo">Categorías</h4>
                <ul class="pie-pagina__menu">
                    <?php foreach(array_slice($categorias_nav, 0, 6) as $cat): ?>
                    <li>
                        <a href="/categoria-producto/categoria?categoria=<?php echo s($cat->slug); ?>">
                            <?php echo s($cat->nombre); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 class="pie-pagina__subtitulo">Mi cuenta</h4>
                <ul class="pie-pagina__menu">
                    <?php if(is_auth()): ?>
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
        <div class="pie-pagina__derechos">
            <p>&copy; <?php echo date('Y'); ?> Ferretería Hardware. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<script src="/js/app.js"></script>
</body>
</html>
