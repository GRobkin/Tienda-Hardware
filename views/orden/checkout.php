<?php
/**
 * Vista: orden/checkout.php
 * Variables: $productos (con ->cantidad y ->subtotal), $total, $alertas
 */
?>

<div class="contenedor">

    <header class="page-header">
        <h1 class="page-header__titulo">Finalizar compra</h1>
        <p class="page-header__meta">Pago de demostración — no se realiza ningún cobro real.</p>
    </header>

    <div class="checkout-layout seccion">

        <section class="checkout-form">
            <h2 class="resumen__titulo">Datos de pago</h2>

            <?php include __DIR__ . '/../parciales/alertas.php'; ?>

            <form method="POST" action="/checkout" id="formCheckout" novalidate>
                <?= csrf_field() ?>

                <div class="campo">
                    <label class="campo__label" for="nombre_pago">Titular de la tarjeta</label>
                    <input class="campo__input"
                           id="nombre_pago"
                           name="nombre_pago"
                           type="text"
                           placeholder="Como figura en la tarjeta"
                           autocomplete="cc-name"
                           required>
                    <span class="campo__error" id="errorTitular"></span>
                </div>

                <div class="campo">
                    <label class="campo__label" for="numeroTarjeta">Número de tarjeta</label>
                    <input class="campo__input"
                           id="numeroTarjeta"
                           name="numero_tarjeta"
                           type="text"
                           inputmode="numeric"
                           placeholder="0000 0000 0000 0000"
                           autocomplete="cc-number"
                           maxlength="19"
                           required>
                    <span class="campo__error" id="errorTarjeta"></span>
                    <span class="campo__ayuda">Demo: ingresá 16 dígitos cualquiera.</span>
                </div>

                <button type="submit" class="boton boton--primario boton--lg boton--bloque">
                    Pagar <?= formatear_precio($total) ?>
                </button>
            </form>
        </section>

        <aside class="resumen">
            <h2 class="resumen__titulo">Tu pedido</h2>

            <ul class="resumen__items">
                <?php foreach ($productos as $producto): ?>
                <li class="resumen__item">
                    <img src="/img/productos/<?= s($producto->imagen) ?>"
                         alt=""
                         onerror="this.onerror=null;this.src='/img/placeholder.svg'">
                    <span class="resumen__item-nombre">
                        <?= s($producto->nombre) ?>
                        <small>× <?= (int) $producto->cantidad ?></small>
                    </span>
                    <span class="resumen__item-precio"><?= formatear_precio($producto->subtotal) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="resumen__linea resumen__linea--total">
                <span>Total</span>
                <span><?= formatear_precio($total) ?></span>
            </div>

            <a href="/carrito" class="boton boton--fantasma boton--sm boton--bloque">← Volver al carrito</a>
        </aside>

    </div>

</div>
