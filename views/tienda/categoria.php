<?php
/**
 * Vista: tienda/categoria.php
 * Variables: $categoria, $subcategorias, $productos (con ->subcategoria cargada)
 */
?>

<div class="contenedor">

    <nav class="breadcrumb" aria-label="Ruta de navegación">
        <a href="/">Inicio</a>
        <span class="breadcrumb__sep">/</span>
        <span class="breadcrumb__actual"><?= s($categoria->nombre) ?></span>
    </nav>

    <header class="page-header">
        <p class="page-header__overline">Categoría</p>
        <h1 class="page-header__titulo"><?= s($categoria->nombre) ?></h1>
        <p class="page-header__meta">
            <?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?>
            <?= $categoria->descripcion ? '— ' . s($categoria->descripcion) : '' ?>
        </p>
    </header>

    <?php if (!empty($subcategorias)): ?>
    <div class="chips">
        <span class="chip chip--activo">Todo</span>
        <?php foreach ($subcategorias as $sub): ?>
        <a class="chip"
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
            <p class="vacio__texto">Estamos sumando productos a esta categoría. Volvé pronto o explorá el resto de la tienda.</p>
            <a href="/" class="boton boton--primario">Volver al inicio</a>
        </div>
    <?php else: ?>
        <div class="grid-productos seccion">
            <?php foreach ($productos as $producto): ?>
                <?php include __DIR__ . '/../parciales/producto-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
