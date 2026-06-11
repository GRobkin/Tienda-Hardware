<?php
/**
 * Vista: tienda/producto.php
 * Variables: $producto (con ->categoria y ->subcategoria), $relacionados
 */
$stock = (int) $producto->stock;
?>

<div class="contenedor">

    <nav class="breadcrumb" aria-label="Ruta de navegación">
        <a href="/">Inicio</a>
        <?php if ($producto->categoria): ?>
            <span class="breadcrumb__sep">/</span>
            <a href="/categoria-producto/categoria?categoria=<?= s($producto->categoria->slug) ?>">
                <?= s($producto->categoria->nombre) ?>
            </a>
        <?php endif; ?>
        <?php if ($producto->subcategoria && $producto->categoria): ?>
            <span class="breadcrumb__sep">/</span>
            <a href="/categoria-producto/subcategoria?categoria=<?= s($producto->categoria->slug) ?>&subcategoria=<?= s($producto->subcategoria->slug) ?>">
                <?= s($producto->subcategoria->nombre) ?>
            </a>
        <?php endif; ?>
        <span class="breadcrumb__sep">/</span>
        <span class="breadcrumb__actual"><?= s($producto->nombre) ?></span>
    </nav>

    <div class="producto-detalle">

        <div class="producto-detalle__media">
            <img src="/img/productos/<?= s($producto->imagen) ?>"
                 alt="<?= s($producto->nombre) ?>"
                 onerror="this.onerror=null;this.src='/img/placeholder.svg'">
        </div>

        <div class="producto-detalle__info">

            <?php if ($producto->subcategoria): ?>
                <p class="page-header__overline"><?= s($producto->subcategoria->nombre) ?></p>
            <?php endif; ?>

            <h1 class="producto-detalle__titulo"><?= s($producto->nombre) ?></h1>

            <p class="producto-detalle__precio"><?= formatear_precio($producto->precio) ?></p>

            <?php if ($stock < 1): ?>
                <span class="stock stock--agotado">Sin stock</span>
            <?php elseif ($stock <= 3): ?>
                <span class="stock stock--bajo">¡Últimas <?= $stock ?> unidades!</span>
            <?php else: ?>
                <span class="stock stock--ok">En stock</span>
            <?php endif; ?>

            <div class="producto-detalle__compra">
                <?php if ($stock > 0): ?>
                    <div class="cantidad">
                        <button type="button" class="cantidad__btn cantidad__btn--menos" aria-label="Restar una unidad">−</button>
                        <input class="cantidad__input"
                               id="productoCantidad"
                               type="number"
                               inputmode="numeric"
                               min="1"
                               max="<?= $stock ?>"
                               value="1"
                               aria-label="Cantidad">
                        <button type="button" class="cantidad__btn cantidad__btn--mas" aria-label="Sumar una unidad">+</button>
                    </div>
                    <button type="button"
                            class="boton boton--primario boton--lg agregar-carrito"
                            data-id="<?= (int) $producto->id ?>"
                            data-cantidad="#productoCantidad">
                        Agregar al carrito
                    </button>
                <?php else: ?>
                    <p class="campo__ayuda">Este producto no está disponible por el momento.</p>
                <?php endif; ?>
            </div>

            <div>
                <h2 class="producto-detalle__descripcion-titulo">Descripción</h2>
                <p class="producto-detalle__descripcion"><?= nl2br(s($producto->descripcion)) ?></p>
            </div>

        </div>
    </div>

    <?php if (!empty($relacionados)): ?>
    <section class="seccion">
        <div class="seccion__header">
            <h2 class="seccion__titulo">Productos relacionados</h2>
        </div>
        <div class="grid-productos">
            <?php
            foreach ($relacionados as $relacionado):
                // Las tarjetas relacionadas comparten subcategoría con el producto actual
                $relacionado->subcategoria = $producto->subcategoria;
                $original = $producto;
                $producto = $relacionado;
                include __DIR__ . '/../parciales/producto-card.php';
                $producto = $original;
            endforeach;
            ?>
        </div>
    </section>
    <?php endif; ?>

</div>
