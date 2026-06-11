<?php
/**
 * Parcial: parciales/producto-card.php
 * Variables: $producto — opcionalmente con ->categoria / ->subcategoria cargadas.
 * Overline: categoría si está disponible; si no, subcategoría.
 */
$overline  = $producto->categoria->nombre ?? ($producto->subcategoria->nombre ?? '');
$sin_stock = (int) $producto->stock < 1;
?>
<article class="producto-card">
    <a href="/producto?id=<?= (int) $producto->id ?>" class="producto-card__media" tabindex="-1">
        <img src="/img/productos/<?= s($producto->imagen) ?>"
             alt="<?= s($producto->nombre) ?>"
             loading="lazy"
             onerror="this.onerror=null;this.src='/img/placeholder.svg'">
    </a>
    <div class="producto-card__body">
        <?php if ($overline): ?>
            <p class="producto-card__overline"><?= s($overline) ?></p>
        <?php endif; ?>
        <h3 class="producto-card__nombre">
            <a href="/producto?id=<?= (int) $producto->id ?>"><?= s($producto->nombre) ?></a>
        </h3>
        <p class="producto-card__precio"><?= formatear_precio($producto->precio) ?></p>
        <?php if ($sin_stock): ?>
            <span class="producto-card__agotado">Sin stock</span>
        <?php else: ?>
            <button type="button"
                    class="boton boton--primario boton--sm agregar-carrito"
                    data-id="<?= (int) $producto->id ?>">
                Agregar al carrito
            </button>
        <?php endif; ?>
    </div>
</article>
