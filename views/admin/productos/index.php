<?php
/**
 * Vista: admin/productos/index.php
 * Variables: $productos, $pagina_actual, $total_paginas
 */
?>

<div class="admin">

    <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

    <div class="admin__seccion">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Productos</h2>
            <a href="/admin/productos/crear" class="admin__accion-btn">+ Nuevo producto</a>
        </div>

        <?php if (empty($productos)): ?>
            <p class="admin__vacio">No hay productos todavía. ¡Creá el primero!</p>
        <?php else: ?>
            <div class="admin__tabla-wrap">
                <table class="admin__tabla">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Destacado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td>
                                <a class="admin__producto"
                                   href="/producto?id=<?= (int) $producto->id ?>"
                                   title="Ver en la tienda">
                                    <img src="<?= imagen_producto($producto) ?>"
                                         alt=""
                                         onerror="this.onerror=null;this.src='/img/placeholder.svg'">
                                    <?= s($producto->nombre) ?>
                                </a>
                            </td>
                            <td>
                                <?= s($producto->categoria->nombre ?? '—') ?>
                                <small class="admin__sub">/ <?= s($producto->subcategoria->nombre ?? '—') ?></small>
                            </td>
                            <td class="admin__monto"><?= formatear_precio($producto->precio) ?></td>
                            <td>
                                <?php if ((int) $producto->stock === 0): ?>
                                    <span class="estado estado--cancelado">Sin stock</span>
                                <?php else: ?>
                                    <?= (int) $producto->stock ?>
                                <?php endif; ?>
                            </td>
                            <td><?= $producto->destacado ? '★' : '—' ?></td>
                            <td>
                                <div class="admin__acciones-fila">
                                    <a class="boton boton--secundario boton--sm"
                                       href="/admin/productos/editar?id=<?= (int) $producto->id ?>">Editar</a>
                                    <form method="POST" action="/admin/productos/eliminar"
                                          class="js-confirm"
                                          data-mensaje="¿Eliminar el producto «<?= s($producto->nombre) ?>»?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $producto->id ?>">
                                        <button type="submit" class="boton boton--peligro boton--sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../parciales/paginacion.php'; ?>

</div>
