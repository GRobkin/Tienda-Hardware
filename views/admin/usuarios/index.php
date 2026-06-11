<?php
/**
 * Vista: admin/usuarios/index.php
 * Variables: $usuarios
 */
?>

<div class="admin">

    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Usuarios registrados</h2>
        </div>

        <?php if (empty($usuarios)): ?>
            <p class="admin__vacio">No hay usuarios registrados.</p>
        <?php else: ?>
            <div class="admin__tabla-wrap">
                <table class="admin__tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Confirmado</th>
                            <th>Rol</th>
                            <th>Alta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= s($usuario->nombre . ' ' . $usuario->apellido) ?></td>
                            <td><?= s($usuario->email) ?></td>
                            <td>
                                <?php if ($usuario->confirmado): ?>
                                    <span class="estado estado--pagado">Sí</span>
                                <?php else: ?>
                                    <span class="estado estado--pendiente">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $usuario->admin ? 'Admin' : 'Cliente' ?></td>
                            <td class="admin__fecha"><?= s($usuario->creado_en ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
