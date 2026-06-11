<?php
/**
 * Vista: admin/categorias/editar.php
 * Variables: $categoria, $alertas
 */
?>

<div class="admin">

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Editar categoría</h2>
            <a href="/admin/categorias" class="admin__ver-mas">← Volver al listado</a>
        </div>

        <div class="admin-form">
            <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

            <form method="POST" action="/admin/categorias/editar?id=<?= (int) $categoria->id ?>" class="form-apilado">
                <?= csrf_field() ?>

                <div class="campo">
                    <label class="campo__label" for="nombre">Nombre</label>
                    <input class="campo__input" id="nombre" name="nombre" type="text"
                           value="<?= s($categoria->nombre) ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="descripcion">Descripción (opcional)</label>
                    <input class="campo__input" id="descripcion" name="descripcion" type="text"
                           value="<?= s($categoria->descripcion) ?>">
                </div>

                <div class="admin-form__acciones">
                    <button type="submit" class="boton boton--primario">Guardar cambios</button>
                    <a href="/admin/categorias" class="boton boton--fantasma">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>
