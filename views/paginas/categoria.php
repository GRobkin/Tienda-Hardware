<?php
/**
 * Vista: paginas/categoria.php
 * Controlador: PaginasController::categoria()
 * Variables: $categoria, $subcategorias, $productos
 */
?>

<!-- ── Encabezado de sección ─────────────────────────────── -->
<section class="cabecera-seccion uk-section uk-section-muted uk-padding-small">
    <div class="uk-container">
        <ul class="uk-breadcrumb">
            <li><a href="/">Inicio</a></li>
            <li><span><?php echo s($categoria->nombre); ?></span></li>
        </ul>
        <h1 class="cabecera-seccion__titulo"><?php echo s($categoria->nombre); ?></h1>
        <?php if($categoria->descripcion): ?>
        <p class="cabecera-seccion__descripcion"><?php echo s($categoria->descripcion); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="uk-container uk-section">
    <div class="uk-grid uk-grid-large" uk-grid>

        <!-- ── Barra lateral: subcategorías ─────────────── -->
        <aside class="uk-width-1-4@m filtros-lateral">
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="filtros-lateral__titulo">Subcategorías</h3>
                <ul class="filtros-lateral__lista">
                    <li>
                        <a href="/categoria-producto/categoria?categoria=<?php echo s($categoria->slug); ?>"
                           class="filtros-lateral__enlace filtros-lateral__enlace--activo">
                            Todas
                        </a>
                    </li>
                    <?php foreach($subcategorias as $sub): ?>
                    <li>
                        <a href="/categoria-producto/subcategoria?categoria=<?php echo s($categoria->slug); ?>&subcategoria=<?php echo s($sub->slug); ?>"
                           class="filtros-lateral__enlace">
                            <?php echo s($sub->nombre); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- ── Grilla de productos ───────────────────────── -->
        <section class="uk-width-3-4@m">

            <?php if(empty($productos)): ?>
            <div class="uk-alert uk-alert-warning">
                <p>No hay productos en esta categoría todavía.</p>
            </div>
            <?php else: ?>

            <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
                <p class="resultados__cantidad">
                    <strong><?php echo count($productos); ?></strong> productos encontrados
                </p>
            </div>

            <div class="grilla-productos uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-3@m" uk-grid>
                <?php foreach($productos as $producto): ?>
                <div>
                    <?php $mostrar_categoria = false; include __DIR__ . '/../parciales/tarjeta-producto.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>
        </section>
    </div>
</div>
