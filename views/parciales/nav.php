<?php
if (empty($categorias_nav)) {
    try {
        $categorias_nav = \Model\Categoria::all('ASC');
    } catch (\Throwable $e) {
        $categorias_nav = [];
    }
}

if (empty($subcategorias_nav)) {
    try {
        $subcategorias_nav = \Model\Subcategoria::all('ASC');
    } catch (\Throwable $e) {
        $subcategorias_nav = [];
    }
}
?>

<nav class="nav" id="nav">

    <!-- Logo -->
    <a href="/" class="nav__logo">
        <img src="/img/logo.png" alt="Tienda Hardware">
    </a>

    <!-- Botón catálogo -->
    <button class="nav__cat-btn" id="catBtn" aria-expanded="false" aria-controls="catalogoPanel">
        <i class="nav__icon nav__icon--menu"></i>
        CATÁLOGO
    </button>

    <!-- Buscador -->
    <div class="nav__search">
        <input id="buscador-input"
               type="search"
               placeholder="Buscar productos..."
               autocomplete="off"
               aria-label="Buscar productos">
        <button type="button" id="btnBuscar" class="nav__search-btn" aria-label="Buscar"><i class="nav__icon nav__icon--search"></i></button>
        <div id="buscador-resultados" class="buscador__resultados" hidden></div>
    </div>

    <!-- Acciones -->
    <div class="nav__actions">

        <!-- Toggle dark mode: por defecto modo claro → muestra luna -->
        <button class="nav__btn" id="themeBtn" aria-label="Cambiar tema">
            <i class="nav__icon nav__icon--moon" id="iconMoon"></i>
            <i class="nav__icon nav__icon--sun"  id="iconSun" hidden></i>
        </button>

        <!-- Carrito -->
        <a href="/carrito" class="nav__btn nav__cart" aria-label="Carrito">
            <i class="nav__icon nav__icon--cart"></i>
            <span class="nav__badge" id="contadorCarrito"
                  <?= total_carrito() > 0 ? '' : 'hidden' ?>>
                <?= total_carrito() ?>
            </span>
        </a>

        <!-- Usuario / Entrar -->
        <?php if (is_auth()): ?>
            <div class="nav__user" id="userMenu">
                <button class="nav__btn nav__btn--user" aria-label="Mi cuenta" aria-expanded="false">
                    <i class="nav__icon nav__icon--user"></i>
                    <span class="nav__username"><?= s($_SESSION['nombre']) ?></span>
                    <i class="nav__icon nav__icon--chevron"></i>
                </button>
                <div class="nav__dropdown" hidden>
                    <a href="/cuenta" class="nav__dropdown-item">Mi cuenta</a>
                    <a href="/mis-pedidos" class="nav__dropdown-item">Mis pedidos</a>
                    <?php if (is_admin()): ?>
                        <hr class="nav__dropdown-sep">
                        <a href="/admin/dashboard" class="nav__dropdown-item">Panel admin</a>
                        <a href="/admin/productos" class="nav__dropdown-item">Productos</a>
                        <a href="/admin/ordenes" class="nav__dropdown-item">Órdenes</a>
                    <?php endif; ?>
                    <hr class="nav__dropdown-sep">
                    <form method="POST" action="/logout">
                        <?= csrf_field() ?>
                        <button type="submit" class="nav__dropdown-item nav__dropdown-item--danger">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <a href="/login" class="nav__btn nav__btn--entrar">
                <i class="nav__icon nav__icon--user"></i>
                Entrar
            </a>
        <?php endif; ?>

    </div>
</nav>

<!-- Overlay -->
<div class="nav__overlay" id="navOverlay" hidden></div>

<!-- Panel catálogo -->
<div class="catalogo-panel" id="catalogoPanel" hidden>
    <div class="catalogo-panel__cats" id="catalogoCats">
        <?php foreach ($categorias_nav as $cat): ?>
        <div class="catalogo-panel__cat"
             data-id="<?= (int)$cat->id ?>"
             data-slug="<?= s($cat->slug) ?>">
            <span><?= s($cat->nombre) ?></span>
            <i class="nav__icon nav__icon--chevron-right"></i>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="catalogo-panel__subs" id="catalogoSubs">
        <p class="catalogo-panel__subs-hint">
            Pasá el mouse sobre una categoría
        </p>
    </div>
</div>

<script>
    window.SUBCATEGORIAS = <?= json_encode(
        array_map(fn($s) => [
            'id'           => (int)$s->id,
            'nombre'       => $s->nombre,
            'slug'         => $s->slug,
            'categoria_id' => (int)$s->categoria_id,
        ], $subcategorias_nav)
    ) ?>;

    window.CATEGORIAS = <?= json_encode(
        array_map(fn($c) => [
            'id'   => (int)$c->id,
            'slug' => $c->slug,
        ], $categorias_nav)
    ) ?>;
</script>