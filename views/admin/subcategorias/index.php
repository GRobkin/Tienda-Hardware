<?php
/**
 * Vista: admin/subcategorias/index.php
 * Variables: $subcategorias (con ->categoria y ->total_productos), $categorias, $alertas
 */
?>

<div class="admin">

    <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Nueva subcategoría</h2>
        </div>
        <form method="POST" action="/admin/subcategorias/crear" class="admin-form admin-form__fila admin-form__fila--crear">
            <?= csrf_field() ?>
            <div class="campo">
                <label class="campo__label" for="nombre">Nombre</label>
                <input class="campo__input" id="nombre" name="nombre" type="text"
                       placeholder="Ej: Refrigeración líquida" required>
            </div>
            <div class="campo">
                <label class="campo__label" for="categoria_id">Categoría</label>
                <select class="campo__select" id="categoria_id" name="categoria_id" required>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria->id ?>"><?= s($categoria->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="boton boton--primario">Crear</button>
        </form>
    </div>

    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Subcategorías</h2>
        </div>

        <div class="admin__tabla-wrap">
            <table class="admin__tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Slug</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subcategorias as $sub): ?>
                    <tr>
                        <td><strong><?= s($sub->nombre) ?></strong></td>
                        <td><?= s($sub->categoria->nombre ?? '—') ?></td>
                        <td><code class="admin__codigo"><?= s($sub->slug) ?></code></td>
                        <td><?= (int) $sub->total_productos ?></td>
                        <td>
                            <div class="admin__acciones-fila">
                                <a class="boton boton--secundario boton--sm"
                                   href="/admin/subcategorias/editar?id=<?= (int) $sub->id ?>">Editar</a>
                                <form method="POST" action="/admin/subcategorias/eliminar"
                                      class="js-confirm"
                                      data-mensaje="¿Eliminar la subcategoría «<?= s($sub->nombre) ?>»?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $sub->id ?>">
                                    <button type="submit" class="boton boton--peligro boton--sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
