<?php

/**
 * Parcial: admin/productos/_form.php
 * Variables: $producto, $categorias, $subcategorias (todas)
 * El <form> lo abre la vista que incluye este parcial.
 */
$subcategoria_actual = null;
foreach ($subcategorias as $sub) {
    if ($sub->id == $producto->subcategoria_id) {
        $subcategoria_actual = $sub;
        break;
    }
}
$categoria_actual_id = $subcategoria_actual->categoria_id ?? ($categorias[0]->id ?? 0);
?>

<?= csrf_field() ?>

<div class="admin-form__grid">

    <div class="admin-form__col">

        <div class="admin-form__fila">
            <div class="campo">
                <label class="campo__label" for="nombre">Nombre</label>
                <input class="campo__input" id="nombre" name="nombre" type="text"
                    value="<?= s($producto->nombre) ?>" placeholder="Ej: AMD Ryzen 7 7800X3D" required>
            </div>

            <div class="campo">
                <label class="campo__label" for="marca">Marca</label>
                <input class="campo__input" id="marca" name="marca" type="text"
                    value="<?= s($producto->marca) ?>" placeholder="Ej: AMD">
            </div>
        </div>

        <div class="campo">
            <label class="campo__label" for="descripcion">Descripción</label>
            <textarea class="campo__textarea" id="descripcion" name="descripcion"
                placeholder="Características principales del producto" required><?= s($producto->descripcion) ?></textarea>
        </div>

        <div class="admin-form__fila">
            <div class="campo">
                <label class="campo__label" for="precio">Precio (US$)</label>
                <input class="campo__input" id="precio" name="precio" type="number"
                    min="0.01" step="0.01" value="<?= s($producto->precio ?: '') ?>" required>
            </div>

            <div class="campo">
                <label class="campo__label" for="stock">Stock</label>
                <input class="campo__input" id="stock" name="stock" type="number"
                    min="0" step="1" value="<?= s($producto->stock !== '' ? $producto->stock : '') ?>" required>
            </div>
        </div>

        <div class="admin-form__fila">
            <div class="campo">
                <label class="campo__label" for="selectCategoria">Categoría</label>
                <select class="campo__select" id="selectCategoria">
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria->id ?>"
                            <?= $categoria->id == $categoria_actual_id ? 'selected' : '' ?>>
                            <?= s($categoria->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label class="campo__label" for="selectSubcategoria">Subcategoría</label>
                <select class="campo__select" id="selectSubcategoria" name="subcategoria_id"
                    data-actual="<?= (int) $producto->subcategoria_id ?>" required></select>
            </div>
        </div>

        <label class="campo campo--checkbox">
            <input type="checkbox" name="destacado" value="1" <?= $producto->destacado ? 'checked' : '' ?>>
            <span class="campo__label">Mostrar en «Destacados» de la portada</span>
        </label>

    </div>

    <div class="admin-form__col admin-form__col--imagen">
        <p class="campo__label">Imagen</p>
        <img id="previewImagen"
            class="admin-form__preview"
            src="<?= imagen_producto($producto) ?>"
            alt="Vista previa"
            onerror="this.onerror=null;this.src='/img/placeholder.svg'">
        <label class="boton boton--secundario boton--sm admin-form__file">
            Elegir imagen…
            <input type="file" id="inputImagen" name="imagen" accept=".jpg,.jpeg,.png,.webp" hidden>
        </label>
        <p class="campo__ayuda">JPG, PNG o WEBP. Se muestra sobre fondo blanco.</p>
    </div>

</div>

<script>
    window.ADMIN_SUBCATS = <?= json_encode(array_map(fn($s) => [
                                'id'           => (int) $s->id,
                                'nombre'       => $s->nombre,
                                'categoria_id' => (int) $s->categoria_id,
                            ], $subcategorias)) ?>;
</script>