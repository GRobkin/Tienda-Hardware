<?php
/**
 * Vista: paginas/index.php (Home)
 * Variables: $destacados, $categorias, $recientes
 */
?>

<!-- Slider de banners -->
<section class="slider">
    <div class="slider__track" id="sliderTrack">

        <div class="slider__slide">
            <img src="/img/banners/BANNER-WEB-KINGSTON.png" alt="Kingston Fury">
        </div>

        <div class="slider__slide">
            <img src="/img/banners/giga-banneeer.png" alt="Gigabyte Monitor">
        </div>

        <div class="slider__slide">
            <img src="/img/banners/470.png" alt="Gigabyte Aorus">
        </div>

    </div>

    <button class="slider__arrow slider__arrow--prev" id="sliderPrev" aria-label="Anterior">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="slider__arrow slider__arrow--next" id="sliderNext" aria-label="Siguiente">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>

    <div class="slider__dots" id="sliderDots"></div>
</section>

<!-- Productos destacados -->
<?php if (!empty($destacados)): ?>
<section class="seccion contenedor">
    <div class="seccion__header">
        <h2 class="seccion__titulo">Destacados</h2>
    </div>
    <div class="grid-productos">
        <?php foreach ($destacados as $producto): ?>
            <?php include __DIR__ . '/../parciales/producto-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Categorías -->
<?php if (!empty($categorias)): ?>
<section class="seccion contenedor">
    <div class="seccion__header">
        <h2 class="seccion__titulo">Explorá por categoría</h2>
    </div>
    <div class="grid-categorias">
        <?php foreach ($categorias as $categoria): ?>
        <a class="categoria-tile" href="/categoria-producto/categoria?categoria=<?= s($categoria->slug) ?>">
            <span class="categoria-tile__nombre"><?= s($categoria->nombre) ?></span>
            <?php if ($categoria->descripcion): ?>
                <span class="categoria-tile__desc"><?= s($categoria->descripcion) ?></span>
            <?php endif; ?>
            <span class="categoria-tile__cta">Ver productos →</span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Recién llegados -->
<?php if (!empty($recientes)): ?>
<section class="seccion contenedor">
    <div class="seccion__header">
        <h2 class="seccion__titulo">Recién llegados</h2>
    </div>
    <div class="grid-productos">
        <?php foreach ($recientes as $producto): ?>
            <?php include __DIR__ . '/../parciales/producto-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
