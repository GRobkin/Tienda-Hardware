<?php
/**
 * Vista: admin/dashboard.php
 * Variables: $total_productos, $total_ordenes, $total_usuarios, $ordenes_recientes
 */
?>

<!-- Tarjetas de estadísticas -->
<div class="uk-grid uk-grid-small uk-child-width-1-3@m uk-margin-bottom" uk-grid>
    <div>
        <div class="tarjeta-estadistica uk-card uk-card-body" style="background:#1976D2;color:#fff">
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div>
                    <p class="tarjeta-estadistica__etiqueta">Productos</p>
                    <h3 class="tarjeta-estadistica__numero"><?php echo (int)$total_productos; ?></h3>
                </div>
                <span uk-icon="icon: tag; ratio:2.5" style="opacity:.4"></span>
            </div>
            <a href="/admin/productos" class="tarjeta-estadistica__enlace">Ver todos →</a>
        </div>
    </div>
    <div>
        <div class="tarjeta-estadistica uk-card uk-card-body" style="background:#388E3C;color:#fff">
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div>
                    <p class="tarjeta-estadistica__etiqueta">Órdenes</p>
                    <h3 class="tarjeta-estadistica__numero"><?php echo (int)$total_ordenes; ?></h3>
                </div>
                <span uk-icon="icon: cart; ratio:2.5" style="opacity:.4"></span>
            </div>
            <a href="/admin/ordenes" class="tarjeta-estadistica__enlace">Ver todas →</a>
        </div>
    </div>
    <div>
        <div class="tarjeta-estadistica uk-card uk-card-body" style="background:#5D4037;color:#fff">
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div>
                    <p class="tarjeta-estadistica__etiqueta">Usuarios</p>
                    <h3 class="tarjeta-estadistica__numero"><?php echo (int)$total_usuarios; ?></h3>
                </div>
                <span uk-icon="icon: users; ratio:2.5" style="opacity:.4"></span>
            </div>
            <a href="/admin/usuarios" class="tarjeta-estadistica__enlace">Ver todos →</a>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="uk-grid uk-grid-small uk-child-width-auto uk-margin-bottom" uk-grid>
    <div><a href="/admin/productos/crear" class="uk-button uk-button-primary">+ Nuevo producto</a></div>
    <div><a href="/admin/categorias/crear" class="uk-button uk-button-default">+ Nueva categoría</a></div>
    <div><a href="/admin/subcategorias/crear" class="uk-button uk-button-default">+ Nueva subcategoría</a></div>
</div>

<!-- Órdenes recientes -->
<div class="uk-card uk-card-default uk-card-body">
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h2 class="uk-card-title uk-margin-remove">Órdenes recientes</h2>
        <a href="/admin/ordenes" class="uk-button uk-button-small uk-button-default">Ver todas</a>
    </div>

    <?php if(empty($ordenes_recientes)): ?>
    <p class="uk-text-muted">No hay órdenes todavía.</p>
    <?php else: ?>
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-small">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Cliente</th>
                    <th class="uk-text-right">Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ordenes_recientes as $orden): ?>
                <tr>
                    <td><code class="uk-text-small"><?php echo s($orden->token); ?></code></td>
                    <td>
                        <?php echo s($orden->usuario->nombre ?? '—'); ?>
                        <?php echo s($orden->usuario->apellido ?? ''); ?>
                    </td>
                    <td class="uk-text-right"><strong><?php echo formatear_precio($orden->total); ?></strong></td>
                    <td>
                        <?php
                        $clase_estado = match($orden->estado) {
                            'pagado'    => 'uk-label-success',
                            'cancelado' => 'uk-label-danger',
                            default     => ''
                        };
                        ?>
                        <span class="uk-label <?php echo $clase_estado; ?>">
                            <?php echo ucfirst(s($orden->estado)); ?>
                        </span>
                    </td>
                    <td class="uk-text-small uk-text-muted">
                        <?php echo s($orden->creado_en ?? '—'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
