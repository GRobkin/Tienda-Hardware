<?php
/**
 * Parcial: tarjeta-producto.php
 * Uso: incluir con $producto en scope
 * Variables:
 *   $producto          — objeto Producto
 *   $mostrar_categoria — bool (opcional)
 */
$mostrar_categoria = $mostrar_categoria ?? false;
?>
<article class="tarjeta-producto">
    <a href="/producto?id=<?php echo (int)$producto->id; ?>" class="tarjeta-producto__imagen-enlace">
        <div class="tarjeta-producto__imagen-contenedor proporcion proporcion--cuadrada">
            <img
                class="tarjeta-producto__imagen"
                src="/img/productos/<?php echo s($producto->imagen ?? 'default.webp'); ?>"
                alt="<?php echo s($producto->nombre); ?>"
                loading="lazy"
            >
            <?php if(($producto->destacado ?? 0)): ?>
            <span class="tarjeta-producto__etiqueta">Destacado</span>
            <?php endif; ?>
        </div>
    </a>

    <div class="tarjeta-producto__cuerpo">
        <?php if($mostrar_categoria && !empty($producto->subcategoria)): ?>
        <span class="tarjeta-producto__categoria">
            <?php echo s($producto->subcategoria->nombre ?? ''); ?>
        </span>
        <?php endif; ?>

        <h3 class="tarjeta-producto__nombre">
            <a href="/producto?id=<?php echo (int)$producto->id; ?>">
                <?php echo s($producto->nombre); ?>
            </a>
        </h3>

        <p class="tarjeta-producto__precio">
            <?php echo formatear_precio($producto->precio); ?>
        </p>

        <div class="tarjeta-producto__acciones">
            <?php if($producto->stock > 0): ?>
            <button
                class="uk-button uk-button-primary uk-button-small boton-agregar-carrito"
                data-id="<?php echo (int)$producto->id; ?>"
                aria-label="Agregar al carrito"
            >
                <span uk-icon="icon: cart; ratio:.8"></span> Agregar
            </button>
            <?php else: ?>
            <span class="tarjeta-producto__sin-stock">Sin stock</span>
            <?php endif; ?>

            <a href="/producto?id=<?php echo (int)$producto->id; ?>"
               class="uk-button uk-button-default uk-button-small">
                Ver
            </a>
        </div>
    </div>
</article>
