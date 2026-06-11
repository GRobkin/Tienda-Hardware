<?php
/**
 * Parcial: parciales/alertas.php — Variable: $alertas
 * Combina las alertas de la vista con los mensajes flash (post-redirect).
 */
$alertas = array_merge_recursive(obtener_flash(), $alertas ?? []);
?>
<div aria-live="polite">
<?php if (!empty($alertas)): ?>
    <?php foreach ($alertas as $tipo => $mensajes): ?>
        <?php foreach ((array) $mensajes as $mensaje): ?>
        <div class="alerta alerta--<?= s($tipo) ?>" role="alert">
            <?= s($mensaje) ?>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>
</div>
