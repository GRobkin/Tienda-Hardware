<?php

/**
 * Vista: orden/confirmacion.php
 * Variables: $orden, $items (con ->producto cargado)
 */
?>

<div class="contenedor">

    <div class="confirmacion seccion">

        <div class="confirmacion__panel">
            <span class="confirmacion__icono" aria-hidden="true">✔</span>
            <h1 class="confirmacion__titulo">¡Compra confirmada!</h1>
            <p class="confirmacion__texto">
                Gracias por tu compra. Tu número de orden es
                <code class="confirmacion__token"><?= s($orden->token) ?></code>
            </p>
            <p class="confirmacion__meta">
                Estado: <span class="estado estado--<?= s($orden->estado) ?>"><?= s($orden->estado) ?></span>
                <?php if ($orden->creado_en): ?>
                    · <?= s($orden->creado_en) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item->producto): ?>
                                    <a class="carrito__producto" href="/producto?id=<?= (int) $item->producto->id ?>">
                                        <img src="<?= imagen_producto($item->producto) ?>"
                                            alt="<?= s($item->producto->nombre) ?>"
                                            onerror="this.onerror=null;this.src='/img/placeholder.svg'">
                                        <span><?= s($item->producto->nombre) ?></span>
                                    </a>
                                <?php else: ?>
                                    Producto eliminado
                                <?php endif; ?>
                            </td>
                            <td>× <?= (int) $item->cantidad ?></td>
                            <td><?= formatear_precio($item->precio_unitario) ?></td>
                            <td><?= formatear_precio($item->precio_unitario * $item->cantidad) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="resumen__linea resumen__linea--total confirmacion__total">
            <span>Total pagado</span>
            <span><?= formatear_precio($orden->total) ?></span>
        </div>

        <div class="confirmacion__acciones">
            <a href="/" class="boton boton--primario">Seguir comprando</a>
            <a href="/mis-pedidos" class="boton boton--secundario">Ver mis pedidos</a>
        </div>

    </div>

</div>