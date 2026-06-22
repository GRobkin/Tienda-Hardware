<?php
/**
 * Vista: usuario/dashboard.php
 * Variables: $usuario, $total_ordenes, $ordenes (últimas 3)
 */
?>

<div class="contenedor contenedor--angosto">

    <header class="page-header">
        <p class="page-header__overline">Mi cuenta</p>
        <h1 class="page-header__titulo">Hola, <?= s($usuario->nombre) ?> 👋</h1>
    </header>

    <div class="cuenta-grid seccion">

        <section class="panel">
            <h2 class="resumen__titulo">Mis datos</h2>
            <dl class="datos-lista">
                <div>
                    <dt>Nombre</dt>
                    <dd><?= s($usuario->nombre . ' ' . $usuario->apellido) ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd><?= s($usuario->email) ?></dd>
                </div>
            </dl>
            <a href="/cuenta/modificar" class="boton boton--secundario boton--sm">Modificar mis datos</a>
        </section>

        <section class="panel">
            <h2 class="resumen__titulo">Mis pedidos (<?= (int) $total_ordenes ?>)</h2>

            <?php if (empty($ordenes)): ?>
                <p class="campo__ayuda">Todavía no hiciste ningún pedido.</p>
                <a href="/" class="boton boton--primario boton--sm">Ir a la tienda</a>
            <?php else: ?>
                <ul class="pedidos-mini">
                    <?php foreach ($ordenes as $orden): ?>
                    <li>
                        <a href="/orden/confirmacion?token=<?= urlencode($orden->token) ?>">
                            <code class="admin__codigo"><?= s($orden->token) ?></code>
                        </a>
                        <span class="estado estado--<?= s($orden->estado) ?>"><?= s($orden->estado) ?></span>
                        <strong><?= formatear_precio($orden->total) ?></strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/mis-pedidos" class="boton boton--fantasma boton--sm">Ver todos →</a>
            <?php endif; ?>
        </section>

    </div>

</div>
