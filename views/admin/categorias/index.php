<?php /** Vista: admin/categorias/index.php — Variables: $categorias */ ?>
<div class="uk-flex uk-flex-between uk-margin-bottom">
    <p><?php echo count($categorias); ?> categorías</p>
    <a href="/admin/categorias/crear" class="uk-button uk-button-primary">+ Nueva categoría</a>
</div>
<div class="uk-card uk-card-default">
<table class="uk-table uk-table-divider uk-table-hover uk-table-small">
    <thead><tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Descripción</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($categorias as $cat): ?>
    <tr>
        <td><?php echo (int)$cat->id; ?></td>
        <td><strong><?php echo s($cat->nombre); ?></strong></td>
        <td><code><?php echo s($cat->slug); ?></code></td>
        <td><?php echo s($cat->descripcion); ?></td>
        <td>
            <a href="/admin/categorias/editar?id=<?php echo (int)$cat->id; ?>" class="uk-button uk-button-small uk-button-default">Editar</a>
            <form method="POST" action="/admin/categorias/eliminar" class="uk-display-inline" onsubmit="return confirm('¿Eliminar categoría?')">
                <input type="hidden" name="id" value="<?php echo (int)$cat->id; ?>">
                <button type="submit" class="uk-button uk-button-small uk-button-danger">Eliminar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
