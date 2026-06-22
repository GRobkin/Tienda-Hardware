<?php
/**
 * Vista: admin/ordenes/index.php
 * Variables: $ordenes, $pagina_actual, $total_paginas
 */
?>

<div class="admin">

    <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Órdenes</h2>
            <a href="/admin/ordenes/crear" class="admin__accion-btn">+ Nueva orden</a>
        </div>

        <?php if (empty($ordenes)): ?>
            <p class="admin__vacio">No hay órdenes todavía.</p>
        <?php else: ?>
            <div class="admin__tabla-wrap">
                <table class="admin__tabla">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Cliente</th>
                            <th>Pago</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $orden): ?>
                        <tr>
                            <td><code class="admin__codigo"><?= s($orden->token) ?></code></td>
                            <td>
                                <?= s($orden->usuario->nombre   ?? '—') ?>
                                <?= s($orden->usuario->apellido ?? '') ?>
                            </td>
                            <td class="admin__fecha"><?= s($orden->numero_tarjeta ?: '—') ?></td>
                            <td class="admin__monto"><?= formatear_precio($orden->total) ?></td>
                            <td>
                                <span class="admin__estado admin__estado--<?= s($orden->estado) ?>">
                                    <?= ucfirst(s($orden->estado)) ?>
                                </span>
                            </td>
                            <td class="admin__fecha"><?= s($orden->creado_en ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../parciales/paginacion.php'; ?>

</div>
