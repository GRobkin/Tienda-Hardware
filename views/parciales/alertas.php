<?php
/**
 * Parcial: alertas.php
 * Muestra mensajes de error / éxito / aviso
 * Variable esperada: $alertas (array asociativo tipo ['error' => [...], 'exito' => [...]])
 */
if(empty($alertas)) return;
?>
<div class="alertas uk-margin">
    <?php foreach($alertas as $tipo => $mensajes): ?>
        <?php foreach($mensajes as $mensaje): ?>
        <div class="uk-alert uk-alert-<?php echo $tipo === 'exito' ? 'success' : 'danger'; ?>" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?php echo s($mensaje); ?></p>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
