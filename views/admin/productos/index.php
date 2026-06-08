<?php
/**
 * Vista: admin/productos/index.php
 * Variables: $productos, $pagina_actual, $total_paginas
 */
?>
<div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
    <p class="uk-text-muted"><?php echo count($productos); ?> productos en esta página</p>
    <a href="/admin/productos/crear" class="uk-button uk-button-primary">
        + Nuevo producto
    </a>
</div>

<div class="uk-card uk-card-default uk-overflow-auto">
    <table class="uk-table uk-table-divider uk-table-hover uk-table-small">
        <thead>
            <tr>
                <th style="width:40px">ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th class="uk-text-right">Precio</th>
                <th class="uk-text-center">Stock</th>
                <th class="uk-text-center">Dest.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($productos as $p): ?>
            <tr>
                <td class="uk-text-muted"><?php echo (int)$p->id; ?></td>
                <td>
                    <div class="uk-flex uk-flex-middle" style="gap:10px">
                        <img src="/img/productos/<?php echo s($p->imagen ?? 'default.webp'); ?>"
                             width="44" height="44"
                             style="object-fit:cover;border-radius:6px;border:1px solid #eee;flex-shrink:0"
                             alt="">
                        <span class="uk-text-bold"><?php echo s($p->nombre); ?></span>
                    </div>
                </td>
                <td>
                    <span class="uk-text-small">
                        <?php echo s($p->categoria->nombre ?? '—'); ?>
                    </span><br>
                    <span class="uk-text-small uk-text-muted">
                        <?php echo s($p->subcategoria->nombre ?? '—'); ?>
                    </span>
                </td>
                <td class="uk-text-right uk-text-bold">
                    <?php echo formatear_precio($p->precio); ?>
                </td>
                <td class="uk-text-center">
                    <span class="<?php echo $p->stock > 0 ? 'uk-text-success' : 'uk-text-danger'; ?> uk-text-bold">
                        <?php echo (int)$p->stock; ?>
                    </span>
                </td>
                <td class="uk-text-center">
                    <?php echo $p->destacado
                        ? '<span class="uk-label uk-label-success" title="Destacado">★</span>'
                        : '<span class="uk-text-muted">—</span>'; ?>
                </td>
                <td>
                    <a href="/admin/productos/editar?id=<?php echo (int)$p->id; ?>"
                       class="uk-button uk-button-small uk-button-default">
                        Editar
                    </a>
                    <form method="POST" action="/admin/productos/eliminar"
                          class="uk-display-inline"
                          onsubmit="return confirm('¿Eliminar «<?php echo s($p->nombre); ?>»? Esta acción no se puede deshacer.')">
                        <input type="hidden" name="id" value="<?php echo (int)$p->id; ?>">
                        <button type="submit" class="uk-button uk-button-small uk-button-danger">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if(($total_paginas ?? 1) > 1): ?>
<div class="uk-flex uk-flex-center uk-margin-top" style="gap:4px">
    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
    <a href="/admin/productos?page=<?php echo $i; ?>"
       class="uk-button uk-button-small <?php echo $i === ($pagina_actual ?? 1) ? 'uk-button-primary' : 'uk-button-default'; ?>">
        <?php echo $i; ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
