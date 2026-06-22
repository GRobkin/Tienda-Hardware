<?php
/**
 * Parcial: parciales/filtros.php — sidebar de filtros de listado
 * Variables: $marcas_disponibles, $filtros, $accion_base (array de hidden inputs), $url_limpiar
 */
?>
<aside class="filtros">
    <form method="GET" class="filtros__form">

        <?php foreach ($accion_base as $campo => $valor): ?>
            <input type="hidden" name="<?= s($campo) ?>" value="<?= s($valor) ?>">
        <?php endforeach; ?>

        <fieldset class="filtros__grupo">
            <legend class="filtros__titulo">Ordenar por</legend>
            <select name="orden" class="campo__input" onchange="this.form.submit()" aria-label="Ordenar productos">
                <?php
                $opciones_orden = [
                    'recientes'   => 'Más recientes',
                    'precio_asc'  => 'Precio: menor a mayor',
                    'precio_desc' => 'Precio: mayor a menor',
                    'nombre'      => 'Nombre (A–Z)',
                ];
                foreach ($opciones_orden as $valor => $etiqueta): ?>
                <option value="<?= s($valor) ?>" <?= $filtros['orden'] === $valor ? 'selected' : '' ?>>
                    <?= s($etiqueta) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </fieldset>

        <?php if (!empty($marcas_disponibles)): ?>
        <fieldset class="filtros__grupo">
            <legend class="filtros__titulo">Marca</legend>
            <?php foreach ($marcas_disponibles as $marca): ?>
            <label class="filtros__opcion">
                <input type="checkbox" name="marca[]" value="<?= s($marca) ?>"
                    <?= in_array($marca, $filtros['marcas']) ? 'checked' : '' ?>>
                <span><?= s($marca) ?></span>
            </label>
            <?php endforeach; ?>
        </fieldset>
        <?php endif; ?>

        <fieldset class="filtros__grupo">
            <legend class="filtros__titulo">Precio (US$)</legend>
            <div class="filtros__precios">
                <input class="campo__input" type="number" name="precio_min" min="0" step="0.01"
                       placeholder="Mín" aria-label="Precio mínimo"
                       value="<?= $filtros['precio_min'] !== null ? s($filtros['precio_min']) : '' ?>">
                <span aria-hidden="true">–</span>
                <input class="campo__input" type="number" name="precio_max" min="0" step="0.01"
                       placeholder="Máx" aria-label="Precio máximo"
                       value="<?= $filtros['precio_max'] !== null ? s($filtros['precio_max']) : '' ?>">
            </div>
        </fieldset>

        <button type="submit" class="boton boton--primario boton--sm boton--bloque">Filtrar</button>

        <?php if ($filtros['marcas'] || $filtros['precio_min'] !== null || $filtros['precio_max'] !== null): ?>
            <a href="<?= s($url_limpiar) ?>" class="boton boton--fantasma boton--sm boton--bloque">Limpiar filtros</a>
        <?php endif; ?>

    </form>
</aside>
