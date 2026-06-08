<?php /** Vista: admin/subcategorias/index.php — Variables: $subcategorias */ ?>
<div class="uk-flex uk-flex-between uk-margin-bottom">
    <p><?php echo count($subcategorias); ?> subcategorías</p>
    <a href="/admin/subcategorias/crear" class="uk-button uk-button-primary">+ Nueva subcategoría</a>
</div>
<div class="uk-card uk-card-default">
<table class="uk-table uk-table-divider uk-table-hover uk-table-small">
    <thead><tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Categoría</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($subcategorias as $sub): ?>
    <tr>
        <td><?php echo (int)$sub->id; ?></td>
        <td><?php echo s($sub->nombre); ?></td>
        <td><code><?php echo s($sub->slug); ?></code></td>
        <td><?php echo s($sub->categoria->nombre ?? '—'); ?></td>
        <td>
            <a href="/admin/subcategorias/editar?id=<?php echo (int)$sub->id; ?>" class="uk-button uk-button-small uk-button-default">Editar</a>
            <form method="POST" action="/admin/subcategorias/eliminar" class="uk-display-inline" onsubmit="return confirm('¿Eliminar subcategoría?')">
                <input type="hidden" name="id" value="<?php echo (int)$sub->id; ?>">
                <button type="submit" class="uk-button uk-button-small uk-button-danger">Eliminar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
