<?php
/**
 * Vista: admin/categorias/index.php
 * Variables: $categorias (con ->total_subcategorias), $alertas
 */
?>

<div class="admin">

    <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Nueva categoría</h2>
        </div>
        <form method="POST" action="/admin/categorias/crear" class="admin-form admin-form__fila admin-form__fila--crear">
            <?= csrf_field() ?>
            <div class="campo">
                <label class="campo__label" for="nombre">Nombre</label>
                <input class="campo__input" id="nombre" name="nombre" type="text"
                       placeholder="Ej: Refrigeración" required>
            </div>
            <div class="campo">
                <label class="campo__label" for="descripcion">Descripción (opcional)</label>
                <input class="campo__input" id="descripcion" name="descripcion" type="text"
                       placeholder="Texto corto para la portada">
            </div>
            <button type="submit" class="boton boton--primario">Crear</button>
        </form>
    </div>

    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Categorías</h2>
        </div>

        <div class="admin__tabla-wrap">
            <table class="admin__tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Subcategorías</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><strong><?= s($categoria->nombre) ?></strong></td>
                        <td><code class="admin__codigo"><?= s($categoria->slug) ?></code></td>
                        <td><?= (int) $categoria->total_subcategorias ?></td>
                        <td>
                            <div class="admin__acciones-fila">
                                <a class="boton boton--secundario boton--sm"
                                   href="/admin/categorias/editar?id=<?= (int) $categoria->id ?>">Editar</a>
                                <form method="POST" action="/admin/categorias/eliminar"
                                      class="js-confirm"
                                      data-mensaje="¿Eliminar la categoría «<?= s($categoria->nombre) ?>»?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $categoria->id ?>">
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
