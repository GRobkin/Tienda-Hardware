<?php
/**
 * Vista: tienda/subcategoria.php
 * Variables: $categoria, $subcategoria, $subcategorias, $productos, $total,
 *            $pagina_actual, $total_paginas, $marcas_disponibles, $filtros, $query_filtros
 */
$url_base = '/categoria-producto/subcategoria?categoria=' . urlencode($categoria->slug)
          . '&subcategoria=' . urlencode($subcategoria->slug);
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
            <?= (int) $total ?> producto<?= (int) $total === 1 ? '' : 's' ?>
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

    <div class="listado-layout seccion">

        <?php
        $accion_base = ['categoria' => $categoria->slug, 'subcategoria' => $subcategoria->slug];
        $url_limpiar = $url_base;
        include __DIR__ . '/../parciales/filtros.php';
        ?>

        <div class="listado-layout__contenido">
            <?php if (empty($productos)): ?>
                <div class="vacio">
                    <span class="vacio__icono" aria-hidden="true">📦</span>
                    <p class="vacio__titulo">No encontramos productos</p>
                    <p class="vacio__texto">Probá con otros filtros o mirá las demás subcategorías de <?= s($categoria->nombre) ?>.</p>
                    <a href="<?= s($url_limpiar) ?>" class="boton boton--primario">Quitar filtros</a>
                </div>
            <?php else: ?>
                <div class="grid-productos">
                    <?php
                    foreach ($productos as $producto):
                        $producto->categoria = $categoria;
                        include __DIR__ . '/../parciales/producto-card.php';
                    endforeach;
                    ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                <nav class="paginacion" aria-label="Paginación">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($i === $pagina_actual): ?>
                            <span class="paginacion__enlace paginacion__enlace--actual" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a class="paginacion__enlace" href="?<?= s($query_filtros) ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
