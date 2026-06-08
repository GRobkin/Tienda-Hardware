<?php
/**
 * Vista: paginas/index.php
 * Variables: $destacados, $categorias, $recientes
 */
?>

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero">
    <div class="uk-container hero__contenido">
        <h1 class="hero__titulo">Tu tienda de <span>hardware</span><br>de confianza</h1>
        <p class="hero__bajada">Componentes, periféricos y tecnología. Envíos a todo el país.</p>
        <div class="hero__botones">
            <a href="#categorias" class="uk-button uk-button-primary uk-button-large hero__boton"
               uk-scroll="offset: 80">Ver categorías</a>
            <a href="/sobre" class="uk-button uk-button-default uk-button-large hero__boton-secundario">
                Sobre nosotros
            </a>
        </div>
    </div>
</section>

<!-- ── Ventajas ──────────────────────────────────────────── -->
<section class="ventajas-strip">
    <div class="uk-container">
        <div class="uk-grid uk-grid-collapse uk-child-width-1-2 uk-child-width-1-4@m" uk-grid>
            <div class="ventaja-strip__item">
                <span uk-icon="icon: star; ratio:1.4" class="ventaja-strip__icono"></span>
                <div>
                    <strong>Productos originales</strong>
                    <p>Solo distribuidores oficiales</p>
                </div>
            </div>
            <div class="ventaja-strip__item">
                <span uk-icon="icon: receiver; ratio:1.4" class="ventaja-strip__icono"></span>
                <div>
                    <strong>Soporte técnico</strong>
                    <p>Antes y después de tu compra</p>
                </div>
            </div>
            <div class="ventaja-strip__item">
                <span uk-icon="icon: cart; ratio:1.4" class="ventaja-strip__icono"></span>
                <div>
                    <strong>Pago seguro</strong>
                    <p>Tus datos siempre protegidos</p>
                </div>
            </div>
            <div class="ventaja-strip__item">
                <span uk-icon="icon: location; ratio:1.4" class="ventaja-strip__icono"></span>
                <div>
                    <strong>Envíos a todo el país</strong>
                    <p>Rápido y con seguimiento</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Categorías ─────────────────────────────────────────── -->
<section id="categorias" class="uk-section uk-section-muted">
    <div class="uk-container">
        <h2 class="seccion__titulo uk-text-center">Explorá por categoría</h2>
        <p class="seccion__subtitulo uk-text-center uk-text-muted">
            Encontrá todo lo que necesitás para tu PC o proyecto
        </p>

        <div class="uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-4@m uk-margin-top" uk-grid>
            <?php foreach($categorias as $cat): ?>
            <div>
                <a href="/categoria-producto/categoria?categoria=<?php echo s($cat->slug); ?>"
                   class="tarjeta-categoria">
                    <div class="tarjeta-categoria__icono">
                        <span uk-icon="icon: tag; ratio:1.8"></span>
                    </div>
                    <h3 class="tarjeta-categoria__nombre"><?php echo s($cat->nombre); ?></h3>
                    <?php if($cat->descripcion): ?>
                    <p class="tarjeta-categoria__descripcion"><?php echo s($cat->descripcion); ?></p>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Productos destacados ──────────────────────────────── -->
<?php if(!empty($destacados)): ?>
<section class="uk-section">
    <div class="uk-container">
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <div>
                <h2 class="seccion__titulo">Productos destacados</h2>
                <p class="seccion__subtitulo uk-text-muted">Selección especial de nuestra tienda</p>
            </div>
        </div>
        <div class="uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-4@m" uk-grid>
            <?php foreach($destacados as $producto): ?>
            <div>
                <?php $mostrar_categoria = true; include __DIR__ . '/../parciales/tarjeta-producto.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Novedades ─────────────────────────────────────────── -->
<?php if(!empty($recientes)): ?>
<section class="uk-section uk-section-muted">
    <div class="uk-container">
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <div>
                <h2 class="seccion__titulo">Novedades</h2>
                <p class="seccion__subtitulo uk-text-muted">Los últimos productos en llegar</p>
            </div>
        </div>
        <div class="uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-4@m" uk-grid>
            <?php foreach($recientes as $producto): ?>
            <div>
                <?php $mostrar_categoria = false; include __DIR__ . '/../parciales/tarjeta-producto.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
