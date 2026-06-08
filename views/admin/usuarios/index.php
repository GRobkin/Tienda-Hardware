<?php /** Vista: admin/usuarios/index.php — Variables: $usuarios */ ?>
<div class="uk-card uk-card-default">
<table class="uk-table uk-table-divider uk-table-small uk-table-hover">
    <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Admin</th><th>Confirmado</th><th>Registro</th></tr></thead>
    <tbody>
    <?php foreach($usuarios as $usr): ?>
    <tr>
        <td><?php echo (int)$usr->id; ?></td>
        <td><?php echo s($usr->nombre); ?> <?php echo s($usr->apellido); ?></td>
        <td><?php echo s($usr->email); ?></td>
        <td><?php echo $usr->admin ? '<span class="uk-label uk-label-warning">Admin</span>' : '—'; ?></td>
        <td><?php echo $usr->confirmado ? '<span class="uk-label uk-label-success">Sí</span>' : '<span class="uk-label uk-label-danger">No</span>'; ?></td>
        <td><?php echo s($usr->creado_en ?? '—'); ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
