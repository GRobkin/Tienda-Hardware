<?php
/**
 * Vista: orden/confirmacion.php
 * Variables: $orden, $items
 */
?>

<div class="confirmacion uk-section uk-text-center">
    <div class="uk-container">

        <div class="confirmacion__icono">
            <span uk-icon="icon: check; ratio: 4" class="uk-text-success"></span>
        </div>

        <h1 class="confirmacion__titulo">¡Compra confirmada!</h1>
        <p class="confirmacion__subtitulo">
            Gracias por tu compra. Tu número de orden es:
            <strong class="confirmacion__token"><?php echo s($orden->token); ?></strong>
        </p>

        <!-- Detalle de items -->
        <div class="uk-width-1-2@m uk-margin-auto uk-margin-top">
            <div class="uk-card uk-card-default uk-card-body uk-text-left">
                <h3>Detalle del pedido</h3>
                <table class="uk-table uk-table-divider uk-table-small">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="uk-text-center">Cant.</th>
                            <th class="uk-text-right">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><?php echo s($item->producto->nombre ?? '—'); ?></td>
                            <td class="uk-text-center"><?php echo (int)$item->cantidad; ?></td>
                            <td class="uk-text-right"><?php echo formatear_precio($item->precio_unitario); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong>Total</strong></td>
                            <td class="uk-text-right"><strong><?php echo formatear_precio($orden->total); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="uk-margin-large-top">
            <a href="/" class="uk-button uk-button-primary">Seguir comprando</a>
            <?php if(is_auth()): ?>
            <a href="/mis-pedidos" class="uk-button uk-button-default uk-margin-left">Ver mis pedidos</a>
            <?php endif; ?>
        </div>

    </div>
</div>
