<?php /** Parcial: parciales/alertas.php — Variable: $alertas */ ?>
 
<?php if (!empty($alertas)): ?>
    <?php foreach ($alertas as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $mensaje): ?>
        <div class="alerta alerta--<?= $tipo ?>">
            <?= s($mensaje) ?>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>