<?php
/**
 * Vista: admin/productos/crear.php
 * Variables: $producto, $categorias, $subcategorias, $alertas
 */
?>

<div class="admin">

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Nuevo producto</h2>
            <a href="/admin/productos" class="admin__ver-mas">← Volver al listado</a>
        </div>

        <div class="admin-form">
            <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

            <form method="POST" action="/admin/productos/crear" enctype="multipart/form-data">
                <?php include __DIR__ . '/_form.php'; ?>
                <div class="admin-form__acciones">
                    <button type="submit" class="boton boton--primario">Crear producto</button>
                    <a href="/admin/productos" class="boton boton--fantasma">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>
