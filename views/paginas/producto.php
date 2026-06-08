<?php
/**
 * Vista: paginas/producto.php
 * FIX: variable $producto pisada en foreach relacionados → renombrado a $rel
 */
?>
<div class="uk-container uk-section">

    <!-- Migas de pan -->
    <ul class="uk-breadcrumb uk-margin-bottom">
        <li><a href="/">Inicio</a></li>
        <?php if(!empty($producto->categoria)): ?>
        <li>
            <a href="/categoria-producto/categoria?categoria=<?php echo s($producto->categoria->slug); ?>">
                <?php echo s($producto->categoria->nombre); ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if(!empty($producto->subcategoria)): ?>
        <li>
            <a href="/categoria-producto/subcategoria?categoria=<?php echo s($producto->categoria->slug ?? ''); ?>&subcategoria=<?php echo s($producto->subcategoria->slug); ?>">
                <?php echo s($producto->subcategoria->nombre); ?>
            </a>
        </li>
        <?php endif; ?>
        <li><span><?php echo s($producto->nombre); ?></span></li>
    </ul>

    <!-- Detalle -->
    <div class="uk-grid uk-grid-large uk-margin-bottom" uk-grid>

        <!-- Imagen -->
        <div class="uk-width-1-2@m">
            <div class="producto-detalle__imagen-contenedor proporcion proporcion--cuadrada">
                <img
                    class="producto-detalle__imagen"
                    src="/img/productos/<?php echo s($producto->imagen ?? 'default.webp'); ?>"
                    alt="<?php echo s($producto->nombre); ?>"
                >
            </div>
        </div>

        <!-- Info -->
        <div class="uk-width-1-2@m">
            <?php if(!empty($producto->subcategoria)): ?>
            <p class="producto-detalle__categoria">
                <a href="/categoria-producto/subcategoria?categoria=<?php echo s($producto->categoria->slug ?? ''); ?>&subcategoria=<?php echo s($producto->subcategoria->slug); ?>">
                    <?php echo s($producto->subcategoria->nombre); ?>
                </a>
            </p>
            <?php endif; ?>

            <h1 class="producto-detalle__nombre"><?php echo s($producto->nombre); ?></h1>

            <p class="producto-detalle__precio"><?php echo formatear_precio($producto->precio); ?></p>

            <div class="producto-detalle__stock uk-margin-small">
                <?php if($producto->stock > 0): ?>
                <span class="etiqueta etiqueta--disponible">
                    ✓ En stock (<?php echo (int)$producto->stock; ?> unidades)
                </span>
                <?php else: ?>
                <span class="etiqueta etiqueta--sin-stock">Sin stock</span>
                <?php endif; ?>
            </div>

            <div class="producto-detalle__descripcion uk-margin">
                <p><?php echo s($producto->descripcion); ?></p>
            </div>

            <?php if($producto->stock > 0): ?>
            <div class="producto-detalle__cantidad uk-flex uk-flex-middle uk-margin">
                <label class="uk-form-label uk-margin-right" for="cantidad-producto">Cantidad:</label>
                <div class="control-cantidad">
                    <button type="button" class="control-cantidad__boton"
                            onclick="modificarCantidad(-1, 'cantidad-producto')">−</button>
                    <input id="cantidad-producto" class="control-cantidad__campo"
                           type="number" value="1" min="1"
                           max="<?php echo (int)$producto->stock; ?>" readonly>
                    <button type="button" class="control-cantidad__boton"
                            onclick="modificarCantidad(1, 'cantidad-producto')">+</button>
                </div>
            </div>
            <button class="uk-button uk-button-primary uk-button-large boton-agregar-carrito"
                    data-id="<?php echo (int)$producto->id; ?>"
                    data-campo="cantidad-producto">
                <span uk-icon="cart"></span> Agregar al carrito
            </button>
            <?php else: ?>
            <p class="uk-text-danger uk-margin-top">
                <span uk-icon="warning"></span> Producto no disponible actualmente.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos relacionados — FIX: variable $rel en lugar de $producto -->
    <?php if(!empty($relacionados)): ?>
    <section class="relacionados uk-margin-large-top">
        <h2 class="seccion__titulo">Productos relacionados</h2>
        <div class="uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-4@m uk-margin-top" uk-grid>
            <?php foreach($relacionados as $rel): ?>
            <div>
                <?php
                // FIX: usar variable separada para no pisar el $producto principal
                $producto_tarjeta = $rel;
                $mostrar_categoria = false;
                ?>
                <article class="tarjeta-producto">
                    <a href="/producto?id=<?php echo (int)$producto_tarjeta->id; ?>" class="tarjeta-producto__imagen-enlace">
                        <div class="tarjeta-producto__imagen-contenedor proporcion proporcion--cuadrada">
                            <img class="tarjeta-producto__imagen"
                                 src="/img/productos/<?php echo s($producto_tarjeta->imagen ?? 'default.webp'); ?>"
                                 alt="<?php echo s($producto_tarjeta->nombre); ?>"
                                 loading="lazy">
                        </div>
                    </a>
                    <div class="tarjeta-producto__cuerpo">
                        <h3 class="tarjeta-producto__nombre">
                            <a href="/producto?id=<?php echo (int)$producto_tarjeta->id; ?>">
                                <?php echo s($producto_tarjeta->nombre); ?>
                            </a>
                        </h3>
                        <p class="tarjeta-producto__precio"><?php echo formatear_precio($producto_tarjeta->precio); ?></p>
                        <div class="tarjeta-producto__acciones">
                            <?php if($producto_tarjeta->stock > 0): ?>
                            <button class="uk-button uk-button-primary boton-agregar-carrito"
                                    data-id="<?php echo (int)$producto_tarjeta->id; ?>">
                                <span uk-icon="cart"></span> Agregar
                            </button>
                            <?php else: ?>
                            <span class="tarjeta-producto__sin-stock">Sin stock</span>
                            <?php endif; ?>
                            <a href="/producto?id=<?php echo (int)$producto_tarjeta->id; ?>"
                               class="uk-button uk-button-default">Ver</a>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>
