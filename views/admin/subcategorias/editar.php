<?php
/**
 * Vista: admin/subcategorias/editar.php
 * Variables: $subcategoria, $categorias, $alertas
 */
?>

<div class="admin">

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Editar subcategoría</h2>
            <a href="/admin/subcategorias" class="admin__ver-mas">← Volver al listado</a>
        </div>

        <div class="admin-form">
            <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

            <form method="POST" action="/admin/subcategorias/editar?id=<?= (int) $subcategoria->id ?>" class="form-apilado">
                <?= csrf_field() ?>

                <div class="campo">
                    <label class="campo__label" for="nombre">Nombre</label>
                    <input class="campo__input" id="nombre" name="nombre" type="text"
                           value="<?= s($subcategoria->nombre) ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="categoria_id">Categoría</label>
                    <select class="campo__select" id="categoria_id" name="categoria_id" required>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria->id ?>"
                                <?= $categoria->id == $subcategoria->categoria_id ? 'selected' : '' ?>>
                                <?= s($categoria->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__label" for="descripcion">Descripción (opcional)</label>
                    <input class="campo__input" id="descripcion" name="descripcion" type="text"
                           value="<?= s($subcategoria->descripcion) ?>">
                </div>

                <div class="admin-form__acciones">
                    <button type="submit" class="boton boton--primario">Guardar cambios</button>
                    <a href="/admin/subcategorias" class="boton boton--fantasma">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>
