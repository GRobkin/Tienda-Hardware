<?php /** Vista: admin/productos/crear.php — Variables: $producto, $categorias, $alertas */ ?>

<a href="/admin/productos" class="uk-button uk-button-default uk-margin-bottom">← Volver</a>

<form method="POST" action="/admin/productos/crear" enctype="multipart/form-data">
    <?php include __DIR__ . '/_formulario.php'; ?>
    <div class="uk-margin-top">
        <button type="submit" class="uk-button uk-button-primary">Guardar producto</button>
        <a href="/admin/productos" class="uk-button uk-button-default uk-margin-left">Cancelar</a>
    </div>
</form>
