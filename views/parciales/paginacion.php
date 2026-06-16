<?php

/**
 * Parcial: parciales/paginacion.php
 * Variables: $pagina_actual, $total_paginas
 */
?>
<?php if (($total_paginas ?? 1) > 1): ?>
    <nav class="paginacion" aria-label="Paginación">
        <?php if ($pagina_actual > 1): ?>
            <a class="paginacion__enlace paginacion__enlace--texto" href="?page=<?= $pagina_actual - 1 ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <?php if ($i === $pagina_actual): ?>
                <span class="paginacion__enlace paginacion__enlace--actual" aria-current="page"><?= $i ?></span>
            <?php else: ?>
                <a class="paginacion__enlace" href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a class="paginacion__enlace paginacion__enlace--texto" href="?page=<?= $pagina_actual + 1 ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>