<?php /** Vista: admin/productos/editar.php — Variables: $producto, $categorias, $subcategorias, $alertas */ ?>

<a href="/admin/productos" class="uk-button uk-button-default uk-margin-bottom">← Volver</a>

<form method="POST" action="/admin/productos/editar?id=<?php echo (int)$producto->id; ?>"
      enctype="multipart/form-data">
    <?php include __DIR__ . '/_formulario.php'; ?>
    <div class="uk-margin-top">
        <button type="submit" class="uk-button uk-button-primary">Actualizar producto</button>
        <a href="/admin/productos" class="uk-button uk-button-default uk-margin-left">Cancelar</a>
    </div>
</form>
