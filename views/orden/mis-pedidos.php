<?php

/**
 * Vista: orden/mis-pedidos.php
 * Variables: $ordenes
 */
?>

<div class="contenedor">

    <header class="page-header">
        <h1 class="page-header__titulo">Mis pedidos</h1>
        <?php if (!empty($ordenes)): ?>
            <p class="page-header__meta">
                <?= count($ordenes) ?> pedido<?= count($ordenes) === 1 ? '' : 's' ?> realizados
            </p>
        <?php endif; ?>
    </header>

    <?php if (empty($ordenes)): ?>

        <div class="vacio seccion">
            <span class="vacio__icono" aria-hidden="true">📦</span>
            <p class="vacio__titulo">Todavía no hiciste ningún pedido</p>
            <p class="vacio__texto">Cuando completes tu primera compra, vas a poder seguirla desde acá.</p>
            <a href="/" class="boton boton--primario">Ir a la tienda</a>
        </div>

    <?php else: ?>

        <div class="tabla-wrap seccion">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th><span class="sr-solo">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $orden): ?>
                        <tr>
                            <td><code class="admin__codigo"><?= s($orden->token) ?></code></td>
                            <td><?= s($orden->creado_en ?? '—') ?></td>
                            <td>
                                <span class="estado estado--<?= s($orden->estado) ?>"><?= s($orden->estado) ?></span>
                            </td>
                            <td><strong><?= formatear_precio($orden->total) ?></strong></td>
                            <td>
                                <a class="seccion__link" href="/orden/confirmacion?token=<?= urlencode($orden->token) ?>">
                                    Ver detalle →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>