<?php
/**
 * Vista: carrito/index.php
 * Variables: $productos (items del carrito), $total
 */
?>
<div class="uk-container uk-section">

    <?php if(empty($productos)): ?>
    <!-- Carrito vacío -->
    <div class="carrito-vacio uk-text-center uk-padding-large">
        <span uk-icon="icon: cart; ratio:4" class="carrito-vacio__icono"></span>
        <h2 class="carrito-vacio__titulo">Tu carrito está vacío</h2>
        <p class="uk-text-muted">Explorá nuestros productos y agregá lo que necesitás.</p>
        <a href="/" class="uk-button uk-button-primary uk-button-large uk-margin-top">
            <span uk-icon="tag"></span> Ver productos
        </a>
    </div>

    <?php else: ?>

    <div class="uk-grid uk-grid-large" uk-grid>

        <!-- ── Items del carrito ──────────────────────────── -->
        <div class="uk-width-2-3@m">
            <h1 class="uk-heading-small uk-margin-bottom">Mi carrito</h1>

            <table class="tabla-carrito uk-table uk-table-divider">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="uk-text-center uk-visible@s">Cantidad</th>
                        <th class="uk-text-right uk-visible@s">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $item): ?>
                    <tr class="tabla-carrito__fila" data-id="<?php echo (int)$item->id; ?>">
                        <td>
                            <div class="uk-flex uk-flex-middle" style="gap:12px">
                                <a href="/producto?id=<?php echo (int)$item->id; ?>">
                                    <img
                                        class="tabla-carrito__imagen"
                                        src="/img/productos/<?php echo s($item->imagen ?? 'default.webp'); ?>"
                                        alt="<?php echo s($item->nombre); ?>"
                                        width="64" height="64"
                                    >
                                </a>
                                <div>
                                    <a href="/producto?id=<?php echo (int)$item->id; ?>"
                                       class="tabla-carrito__nombre">
                                        <?php echo s($item->nombre); ?>
                                    </a>
                                    <p class="tabla-carrito__precio-unitario">
                                        <?php echo formatear_precio($item->precio); ?> c/u
                                    </p>
                                    <!-- Cantidad inline en mobile -->
                                    <div class="control-cantidad uk-hidden@s uk-margin-small-top">
                                        <button type="button"
                                            class="control-cantidad__boton boton-actualizar-carrito"
                                            data-id="<?php echo (int)$item->id; ?>"
                                            data-delta="-1">−</button>
                                        <span class="control-cantidad__valor"><?php echo (int)$item->cantidad; ?></span>
                                        <button type="button"
                                            class="control-cantidad__boton boton-actualizar-carrito"
                                            data-id="<?php echo (int)$item->id; ?>"
                                            data-delta="1">+</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="uk-text-center uk-visible@s">
                            <div class="control-cantidad">
                                <button type="button"
                                    class="control-cantidad__boton boton-actualizar-carrito"
                                    data-id="<?php echo (int)$item->id; ?>"
                                    data-delta="-1">−</button>
                                <span class="control-cantidad__valor"><?php echo (int)$item->cantidad; ?></span>
                                <button type="button"
                                    class="control-cantidad__boton boton-actualizar-carrito"
                                    data-id="<?php echo (int)$item->id; ?>"
                                    data-delta="1">+</button>
                            </div>
                        </td>
                        <td class="tabla-carrito__subtotal uk-text-right uk-visible@s">
                            <?php echo formatear_precio($item->subtotal); ?>
                        </td>
                        <td class="uk-text-center">
                            <button
                                type="button"
                                class="boton-eliminar-carrito uk-icon-button"
                                data-id="<?php echo (int)$item->id; ?>"
                                uk-tooltip="Eliminar"
                                aria-label="Eliminar <?php echo s($item->nombre); ?>"
                                style="color:#f44336"
                            >
                                <span uk-icon="trash"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="uk-flex uk-flex-between uk-margin-top" style="flex-wrap:wrap; gap:8px">
                <a href="/" class="uk-button uk-button-default">
                    <span uk-icon="arrow-left"></span> Seguir comprando
                </a>
                <button type="button" id="boton-vaciar-carrito" class="uk-button uk-button-danger">
                    <span uk-icon="trash"></span> Vaciar carrito
                </button>
            </div>
        </div>

        <!-- ── Resumen ────────────────────────────────────── -->
        <div class="uk-width-1-3@m">
            <div class="resumen-pedido uk-card uk-card-default uk-card-body uk-position-sticky" style="top:90px">
                <h3 class="resumen-pedido__titulo">Resumen del pedido</h3>

                <div class="resumen-pedido__linea uk-flex uk-flex-between">
                    <span>Productos (<?php echo array_sum(array_column($productos, 'cantidad')); ?>)</span>
                    <span><?php echo formatear_precio($total); ?></span>
                </div>
                <div class="resumen-pedido__linea uk-flex uk-flex-between">
                    <span>Envío</span>
                    <span class="uk-text-success">A calcular</span>
                </div>

                <hr class="uk-margin-small">

                <div class="resumen-pedido__total uk-flex uk-flex-between">
                    <strong>Total</strong>
                    <strong class="resumen-pedido__precio-total">
                        <?php echo formatear_precio($total); ?>
                    </strong>
                </div>

                <?php if(is_auth()): ?>
                <a href="/checkout"
                   class="uk-button uk-button-primary uk-width-1-1 uk-margin-top uk-button-large">
                    Finalizar compra →
                </a>
                <?php else: ?>
                <a href="/login"
                   class="uk-button uk-button-primary uk-width-1-1 uk-margin-top uk-button-large">
                    Iniciá sesión para continuar
                </a>
                <p class="uk-text-small uk-text-muted uk-margin-small-top uk-text-center">
                    ¿No tenés cuenta? <a href="/registro">Registrate gratis</a>
                </p>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php endif; ?>
</div>
