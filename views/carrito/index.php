<?php
/**
 * Vista: carrito/index.php
 * Variables: $productos (con ->cantidad y ->subtotal), $total
 */
?>

<div class="contenedor">

    <header class="page-header">
        <h1 class="page-header__titulo">Mi carrito</h1>
        <?php if (!empty($productos)): ?>
        <p class="page-header__meta">
            <?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?> en el carrito
        </p>
        <?php endif; ?>
    </header>

    <?php if (empty($productos)): ?>

        <div class="vacio seccion">
            <span class="vacio__icono" aria-hidden="true">🛒</span>
            <p class="vacio__titulo">Tu carrito está vacío</p>
            <p class="vacio__texto">Explorá el catálogo y agregá los productos que te gusten. Acá los vas a ver listos para comprar.</p>
            <a href="/" class="boton boton--primario">Ir a la tienda</a>
        </div>

    <?php else: ?>

        <div class="carrito-layout seccion">

            <div class="tabla-wrap">
                <table class="tabla" id="tablaCarrito">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th><span class="sr-solo">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td>
                                <a class="carrito__producto" href="/producto?id=<?= (int) $producto->id ?>">
                                    <img src="/img/productos/<?= s($producto->imagen) ?>"
                                         alt="<?= s($producto->nombre) ?>"
                                         onerror="this.onerror=null;this.src='/img/placeholder.svg'">
                                    <span><?= s($producto->nombre) ?></span>
                                </a>
                            </td>
                            <td><?= formatear_precio($producto->precio) ?></td>
                            <td>
                                <div class="cantidad cantidad--sm" data-id="<?= (int) $producto->id ?>">
                                    <button type="button" class="cantidad__btn cantidad__btn--menos" aria-label="Restar una unidad">−</button>
                                    <input class="cantidad__input"
                                           type="number"
                                           inputmode="numeric"
                                           min="0"
                                           max="<?= (int) $producto->stock ?>"
                                           value="<?= (int) $producto->cantidad ?>"
                                           aria-label="Cantidad de <?= s($producto->nombre) ?>"
                                           readonly>
                                    <button type="button" class="cantidad__btn cantidad__btn--mas" aria-label="Sumar una unidad">+</button>
                                </div>
                            </td>
                            <td class="carrito__subtotal"><?= formatear_precio($producto->subtotal) ?></td>
                            <td>
                                <button type="button"
                                        class="carrito__btn-eliminar"
                                        data-id="<?= (int) $producto->id ?>"
                                        aria-label="Eliminar <?= s($producto->nombre) ?> del carrito">
                                    ✕
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <aside class="resumen">
                <h2 class="resumen__titulo">Resumen</h2>
                <div class="resumen__linea">
                    <span>Subtotal</span>
                    <span><?= formatear_precio($total) ?></span>
                </div>
                <div class="resumen__linea">
                    <span>Envío</span>
                    <span>A coordinar</span>
                </div>
                <div class="resumen__linea resumen__linea--total">
                    <span>Total</span>
                    <span><?= formatear_precio($total) ?></span>
                </div>
                <a href="/checkout" class="boton boton--primario boton--lg boton--bloque">
                    Finalizar compra
                </a>
                <button type="button" id="btnVaciarCarrito" class="boton boton--fantasma boton--sm boton--bloque">
                    Vaciar carrito
                </button>
            </aside>

        </div>

    <?php endif; ?>

</div>
