<?php
/**
 * Vista: orden/mis-pedidos.php
 * Variables: $ordenes
 */
?>

<section class="cabecera-seccion uk-section uk-section-muted uk-padding-small">
    <div class="uk-container">
        <h1 class="cabecera-seccion__titulo">Mis pedidos</h1>
    </div>
</section>

<div class="uk-container uk-section">
    <?php if(empty($ordenes)): ?>
    <div class="uk-text-center uk-padding-large">
        <span uk-icon="icon: list; ratio: 3"></span>
        <h2>No tenés pedidos todavía</h2>
        <a href="/" class="uk-button uk-button-primary uk-margin-top">Ir a la tienda</a>
    </div>
    <?php else: ?>
    <table class="uk-table uk-table-divider uk-table-responsive">
        <thead>
            <tr>
                <th>Orden</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($ordenes as $orden): ?>
            <tr>
                <td><strong><?php echo s($orden->token); ?></strong></td>
                <td><?php echo s($orden->creado_en ?? '—'); ?></td>
                <td><?php echo formatear_precio($orden->total); ?></td>
                <td>
                    <span class="etiqueta etiqueta--<?php echo $orden->estado === 'pagado' ? 'disponible' : ($orden->estado === 'cancelado' ? 'sin-stock' : 'pendiente'); ?>">
                        <?php echo ucfirst(s($orden->estado)); ?>
                    </span>
                </td>
                <td>
                    <a href="/orden/confirmacion?token=<?php echo urlencode($orden->token); ?>"
                       class="uk-button uk-button-small uk-button-default">
                        Ver detalle
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
