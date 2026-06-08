<?php /** Vista: admin/categorias/crear.php — Variables: $categoria, $alertas */ ?>
<a href="/admin/categorias" class="uk-button uk-button-default uk-margin-bottom">← Volver</a>
<?php include __DIR__ . '/../../parciales/alertas.php'; ?>
<form method="POST" action="/admin/categorias/crear">
<div class="uk-card uk-card-default uk-card-body">
    <div class="uk-margin">
        <label class="uk-form-label" for="nombre">Nombre *</label>
        <input id="nombre" name="nombre" class="uk-input" type="text" value="<?php echo s($categoria->nombre ?? ''); ?>" required>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="slug">Slug * <small class="uk-text-muted">(URL amigable)</small></label>
        <input id="slug" name="slug" class="uk-input" type="text" value="<?php echo s($categoria->slug ?? ''); ?>" required placeholder="ej: componentes">
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="descripcion">Descripción</label>
        <input id="descripcion" name="descripcion" class="uk-input" type="text" value="<?php echo s($categoria->descripcion ?? ''); ?>">
    </div>
</div>
<div class="uk-margin-top">
    <button type="submit" class="uk-button uk-button-primary">Guardar</button>
    <a href="/admin/categorias" class="uk-button uk-button-default uk-margin-left">Cancelar</a>
</div>
</form>
