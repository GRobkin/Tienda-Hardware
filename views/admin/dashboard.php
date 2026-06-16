<?php

/**
 * Vista: admin/dashboard.php
 * Variables: $total_productos, $total_ordenes, $total_usuarios, $ordenes_recientes
 */
?>

<div class="admin">

    <!-- Tarjetas resumen -->
    <div class="admin__cards">

        <div class="admin__card admin__card--azul">
            <div class="admin__card-info">
                <p class="admin__card-label">Productos</p>
                <h3 class="admin__card-numero"><?= (int) $total_productos ?></h3>
            </div>
            <svg class="admin__card-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <path d="M16 10a4 4 0 0 1-8 0" />
            </svg>
            <a href="/admin/productos" class="admin__card-link">Ver todos →</a>
        </div>

        <div class="admin__card admin__card--verde">
            <div class="admin__card-info">
                <p class="admin__card-label">Órdenes</p>
                <h3 class="admin__card-numero"><?= (int) $total_ordenes ?></h3>
            </div>
            <svg class="admin__card-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <a href="/admin/ordenes" class="admin__card-link">Ver todas →</a>
        </div>

        <div class="admin__card admin__card--violeta">
            <div class="admin__card-info">
                <p class="admin__card-label">Usuarios</p>
                <h3 class="admin__card-numero"><?= (int) $total_usuarios ?></h3>
            </div>
            <svg class="admin__card-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span class="admin__card-link">Registrados</span>
        </div>

    </div>

    <!-- Órdenes recientes -->
    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Órdenes recientes</h2>
            <a href="/admin/ordenes" class="admin__ver-mas">Ver todas →</a>
        </div>

        <?php if (empty($ordenes_recientes)): ?>
            <p class="admin__vacio">No hay órdenes todavía.</p>
        <?php else: ?>
            <div class="admin__tabla-wrap">
                <table class="admin__tabla">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes_recientes as $orden): ?>
                            <tr>
                                <td>
                                    <code class="admin__codigo"><?= s($orden->token) ?></code>
                                </td>
                                <td>
                                    <?= s($orden->usuario->nombre   ?? '—') ?>
                                    <?= s($orden->usuario->apellido ?? '') ?>
                                </td>
                                <td class="admin__monto">
                                    <?= formatear_precio($orden->total) ?>
                                </td>
                                <td>
                                    <span class="admin__estado admin__estado--<?= $orden->estado ?>">
                                        <?= ucfirst(s($orden->estado)) ?>
                                    </span>
                                </td>
                                <td class="admin__fecha">
                                    <?= s($orden->creado_en ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>