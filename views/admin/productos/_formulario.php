<?php
/**
 * Parcial: admin/productos/_formulario.php
 * FIX: select categoría preselecciona correctamente en editar
 *      El controller en editar pasa $subcategoria_actual con categoria_id
 */
$categoria_seleccionada = null;
if(!empty($producto->subcategoria_id)) {
    // Intentar obtener la categoría de la subcategoría actual
    try {
        $sub_actual = \Model\Subcategoria::find($producto->subcategoria_id);
        if($sub_actual) $categoria_seleccionada = $sub_actual->categoria_id;
    } catch(\Throwable $e) {
        $categoria_seleccionada = null;
    }
}
?>

<?php include __DIR__ . '/../../parciales/alertas.php'; ?>

<div class="uk-card uk-card-default uk-card-body">

    <div class="uk-margin">
        <label class="uk-form-label" for="nombre">Nombre del producto *</label>
        <input id="nombre" name="nombre" class="uk-input" type="text"
               value="<?php echo s($producto->nombre ?? ''); ?>" required
               placeholder="Ej: Intel Core i7-13700K">
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" class="uk-textarea" rows="4"
                  required placeholder="Descripción del producto..."><?php echo s($producto->descripcion ?? ''); ?></textarea>
    </div>

    <div class="uk-grid uk-child-width-1-2@m uk-margin" uk-grid>
        <div>
            <label class="uk-form-label" for="precio">Precio (USD) *</label>
            <div class="uk-inline uk-width-1-1">
                <span class="uk-form-icon">$</span>
                <input id="precio" name="precio" class="uk-input" type="number"
                       step="0.01" min="0.01"
                       value="<?php echo s($producto->precio ?? ''); ?>" required>
            </div>
        </div>
        <div>
            <label class="uk-form-label" for="stock">Stock *</label>
            <input id="stock" name="stock" class="uk-input" type="number" min="0"
                   value="<?php echo (int)($producto->stock ?? 0); ?>" required>
        </div>
    </div>

    <!-- FIX: categoría preseleccionada correctamente -->
    <div class="uk-margin">
        <label class="uk-form-label" for="categoria-select">Categoría *</label>
        <select id="categoria-select" class="uk-select"
                onchange="cargarSubcategorias(this.value)">
            <option value="">— Seleccioná una categoría —</option>
            <?php foreach($categorias as $cat): ?>
            <option value="<?php echo (int)$cat->id; ?>"
                <?php echo $categoria_seleccionada == $cat->id ? 'selected' : ''; ?>>
                <?php echo s($cat->nombre); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="subcategoria_id">Subcategoría *</label>
        <select id="subcategoria_id" name="subcategoria_id" class="uk-select" required>
            <?php if(!empty($subcategorias)): ?>
                <option value="">— Elegí una subcategoría —</option>
                <?php foreach($subcategorias as $sub): ?>
                <option value="<?php echo (int)$sub->id; ?>"
                    <?php echo ($producto->subcategoria_id ?? null) == $sub->id ? 'selected' : ''; ?>>
                    <?php echo s($sub->nombre); ?>
                </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">— Primero elegí una categoría —</option>
            <?php endif; ?>
        </select>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label">Imagen del producto</label>
        <?php if(!empty($producto->imagen) && $producto->imagen !== 'default.webp'): ?>
        <div class="uk-flex uk-flex-middle uk-margin-small-bottom" style="gap:12px">
            <img src="/img/productos/<?php echo s($producto->imagen); ?>"
                 width="80" height="80"
                 style="object-fit:cover;border-radius:6px;border:1px solid #eee"
                 alt="Imagen actual">
            <span class="uk-text-muted uk-text-small">Imagen actual. Subí otra para reemplazarla.</span>
        </div>
        <?php endif; ?>
        <input name="imagen" class="uk-input" type="file" accept="image/jpeg,image/png,image/webp">
        <p class="uk-text-small uk-text-muted uk-margin-small-top">
            Formatos: JPG, PNG, WebP. Tamaño recomendado: 600×600px.
        </p>
    </div>

    <div class="uk-margin">
        <label class="uk-flex uk-flex-middle" style="gap:8px; cursor:pointer">
            <input name="destacado" type="checkbox" class="uk-checkbox"
                   value="1" <?php echo ($producto->destacado ?? 0) ? 'checked' : ''; ?>>
            <span>Producto destacado <small class="uk-text-muted">(aparece en la sección destacados de la home)</small></span>
        </label>
    </div>
</div>
