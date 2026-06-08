<?php /** Vista: admin/ordenes/index.php — Variables: $ordenes, $pagina_actual, $total_paginas */ ?>
<div class="uk-card uk-card-default">
<table class="uk-table uk-table-divider uk-table-small uk-table-responsive">
    <thead><tr><th>Token</th><th>Usuario</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead>
    <tbody>
    <?php foreach($ordenes as $orden): ?>
    <tr>
        <td><code><?php echo s($orden->token); ?></code></td>
        <td><?php echo s($orden->usuario->nombre ?? '—'); ?> <?php echo s($orden->usuario->apellido ?? ''); ?></td>
        <td><?php echo formatear_precio($orden->total); ?></td>
        <td><span class="uk-label <?php echo $orden->estado === 'pagado' ? 'uk-label-success' : ($orden->estado === 'cancelado' ? 'uk-label-danger' : ''); ?>"><?php echo ucfirst(s($orden->estado)); ?></span></td>
        <td><?php echo s($orden->creado_en ?? '—'); ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if(($total_paginas ?? 1) > 1): ?>
<div class="uk-flex uk-flex-center uk-margin-top">
    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
    <a href="/admin/ordenes?page=<?php echo $i; ?>" class="uk-button uk-button-small <?php echo $i === ($pagina_actual ?? 1) ? 'uk-button-primary' : 'uk-button-default'; ?> uk-margin-small-right"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
