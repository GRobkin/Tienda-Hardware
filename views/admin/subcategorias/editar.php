<?php /** Vista: admin/subcategorias/editar.php — Variables: $subcategoria, $categorias, $alertas */ ?>
<a href="/admin/subcategorias" class="uk-button uk-button-default uk-margin-bottom">← Volver</a>
<?php include __DIR__ . '/../../parciales/alertas.php'; ?>
<form method="POST" action="/admin/subcategorias/editar?id=<?php echo (int)$subcategoria->id; ?>">
<div class="uk-card uk-card-default uk-card-body">
    <div class="uk-margin">
        <label class="uk-form-label" for="nombre">Nombre *</label>
        <input id="nombre" name="nombre" class="uk-input" type="text" value="<?php echo s($subcategoria->nombre ?? ''); ?>" required>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="slug">Slug *</label>
        <input id="slug" name="slug" class="uk-input" type="text" value="<?php echo s($subcategoria->slug ?? ''); ?>" required>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="categoria_id">Categoría *</label>
        <select id="categoria_id" name="categoria_id" class="uk-select" required>
            <?php foreach($categorias as $cat): ?>
            <option value="<?php echo (int)$cat->id; ?>" <?php echo $subcategoria->categoria_id == $cat->id ? 'selected' : ''; ?>>
                <?php echo s($cat->nombre); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="uk-margin-top">
    <button type="submit" class="uk-button uk-button-primary">Actualizar</button>
    <a href="/admin/subcategorias" class="uk-button uk-button-default uk-margin-left">Cancelar</a>
</div>
</form>
