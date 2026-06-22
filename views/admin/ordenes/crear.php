<?php
/**
 * Vista: admin/ordenes/crear.php
 * Variables: $usuarios, $productos, $alertas
 */
?>

<div class="admin">

    <div class="admin__seccion admin__seccion--form">
        <div class="admin__seccion-header">
            <h2 class="admin__seccion-titulo">Nueva orden manual</h2>
            <a href="/admin/ordenes" class="admin__ver-mas">← Volver al listado</a>
        </div>

        <div class="admin-form">
            <?php include __DIR__ . '/../../parciales/alertas.php'; ?>

            <form method="POST" action="/admin/ordenes/crear" id="formOrdenManual">
                <?= csrf_field() ?>

                <div class="admin-form__fila">
                    <div class="campo">
                        <label class="campo__label" for="usuario_id">Cliente</label>
                        <select class="campo__select" id="usuario_id" name="usuario_id" required>
                            <option value="">— Elegir cliente —</option>
                            <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= (int) $usuario->id ?>">
                                <?= s($usuario->nombre . ' ' . $usuario->apellido) ?> (<?= s($usuario->email) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__label" for="estado">Estado</label>
                        <select class="campo__select" id="estado" name="estado">
                            <option value="pagado">Pagado</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                </div>

                <fieldset class="campo">
                    <legend class="campo__label">Productos</legend>

                    <div id="ordenItems" class="orden-items">
                        <div class="orden-items__fila">
                            <select class="campo__select" name="producto_id[]" required>
                                <option value="">— Elegir producto —</option>
                                <?php foreach ($productos as $producto): ?>
                                <option value="<?= (int) $producto->id ?>" data-precio="<?= s($producto->precio) ?>">
                                    <?= s($producto->nombre) ?> — <?= formatear_precio($producto->precio) ?> (stock: <?= (int) $producto->stock ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input class="campo__input" type="number" name="cantidad[]" min="1" value="1"
                                   aria-label="Cantidad" required>
                            <button type="button" class="boton boton--peligro boton--sm orden-items__quitar"
                                    aria-label="Quitar fila">✕</button>
                        </div>
                    </div>

                    <button type="button" id="btnAgregarItem" class="boton boton--secundario boton--sm">
                        + Agregar producto
                    </button>
                </fieldset>

                <p class="campo__ayuda">
                    Total estimado: <strong id="ordenTotal">US$ 0,00</strong>
                    — el total final se calcula en el servidor con los precios actuales.
                </p>

                <div class="admin-form__acciones">
                    <button type="submit" class="boton boton--primario">Crear orden</button>
                    <a href="/admin/ordenes" class="boton boton--fantasma">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>
