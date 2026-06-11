<?php
/**
 * Vista: tienda/subcategoria.php
 * Variables: $categoria, $subcategoria, $subcategorias (hermanas), $productos
 */
?>

<div class="contenedor">

    <nav class="breadcrumb" aria-label="Ruta de navegación">
        <a href="/">Inicio</a>
        <span class="breadcrumb__sep">/</span>
        <a href="/categoria-producto/categoria?categoria=<?= s($categoria->slug) ?>"><?= s($categoria->nombre) ?></a>
        <span class="breadcrumb__sep">/</span>
        <span class="breadcrumb__actual"><?= s($subcategoria->nombre) ?></span>
    </nav>

    <header class="page-header">
        <p class="page-header__overline"><?= s($categoria->nombre) ?></p>
        <h1 class="page-header__titulo"><?= s($subcategoria->nombre) ?></h1>
        <p class="page-header__meta">
            <?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?>
        </p>
    </header>

    <?php if (!empty($subcategorias)): ?>
    <div class="chips">
        <a class="chip" href="/categoria-producto/categoria?categoria=<?= s($categoria->slug) ?>">Todo</a>
        <?php foreach ($subcategorias as $sub): ?>
        <a class="chip <?= $sub->id === $subcategoria->id ? 'chip--activo' : '' ?>"
           href="/categoria-producto/subcategoria?categoria=<?= s($categoria->slug) ?>&subcategoria=<?= s($sub->slug) ?>">
            <?= s($sub->nombre) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <div class="vacio">
            <span class="vacio__icono" aria-hidden="true">📦</span>
            <p class="vacio__titulo">Todavía no hay productos acá</p>
            <p class="vacio__texto">Estamos sumando productos a <?= s($subcategoria->nombre) ?>. Mientras tanto, mirá las otras subcategorías de <?= s($categoria->nombre) ?>.</p>
            <a href="/categoria-producto/categoria?categoria=<?= s($categoria->slug) ?>" class="boton boton--primario">
                Ver toda la categoría
            </a>
        </div>
    <?php else: ?>
        <div class="grid-productos seccion">
            <?php
            foreach ($productos as $producto):
                // Overline de cada tarjeta: la categoría de la página
                $producto->categoria = $categoria;
                include __DIR__ . '/../parciales/producto-card.php';
            endforeach;
            ?>
        </div>
    <?php endif; ?>

</div>
