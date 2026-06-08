<?php
/**
 * Vista: orden/checkout.php
 * Variables: $productos, $total, $alertas
 */
?>
<div class="uk-container uk-section">
    <h1 class="uk-heading-small uk-text-center uk-margin-bottom">Finalizar compra</h1>

    <?php include __DIR__ . '/../parciales/alertas.php'; ?>

    <div class="uk-grid uk-grid-large" uk-grid>

        <!-- ── Formulario de pago ─────────────────────────── -->
        <div class="uk-width-2-3@m">
            <form method="POST" action="/checkout" id="formulario-pago">

                <!-- Paso 1: Datos de entrega -->
                <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                    <h2 class="pago__titulo">
                        <span class="pago__numero">1</span> Datos de contacto
                    </h2>
                    <?php if(is_auth()): ?>
                    <div class="uk-alert uk-alert-primary">
                        <p>Comprando como <strong><?php echo s($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')); ?></strong>
                        (<?php echo s($_SESSION['email'] ?? ''); ?>)</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Paso 2: Pago ficticio -->
                <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                    <h2 class="pago__titulo">
                        <span class="pago__numero">2</span> Datos de pago
                    </h2>

                    <div class="uk-alert uk-alert-warning">
                        <span uk-icon="warning"></span>
                        <strong>Demo:</strong> No ingreses datos reales. Este es un formulario de ejemplo.
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="nombre-titular">
                            Nombre del titular *
                        </label>
                        <input id="nombre-titular" name="nombre_pago" class="uk-input"
                               type="text" placeholder="Como figura en la tarjeta" required>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="numero-tarjeta">
                            Número de tarjeta *
                        </label>
                        <input id="numero-tarjeta" name="numero_tarjeta"
                               class="uk-input campo-tarjeta"
                               type="text" placeholder="0000 0000 0000 0000"
                               maxlength="19" required>
                    </div>

                    <div class="uk-grid uk-child-width-1-2@s" uk-grid>
                        <div>
                            <label class="uk-form-label" for="vencimiento">Vencimiento</label>
                            <input id="vencimiento" class="uk-input" type="text"
                                   placeholder="MM/AA" maxlength="5">
                        </div>
                        <div>
                            <label class="uk-form-label" for="cvv">Código de seguridad</label>
                            <input id="cvv" class="uk-input" type="text"
                                   placeholder="CVV" maxlength="4">
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="uk-button uk-button-primary uk-button-large uk-width-1-1">
                    <span uk-icon="lock"></span> Confirmar compra — <?php echo formatear_precio($total); ?>
                </button>

                <p class="uk-text-small uk-text-muted uk-text-center uk-margin-small-top">
                    <span uk-icon="icon:lock; ratio:.8"></span>
                    Transacción segura. Al confirmar aceptás los términos y condiciones.
                </p>
            </form>
        </div>

        <!-- ── Resumen del pedido ─────────────────────────── -->
        <div class="uk-width-1-3@m">
            <div class="resumen-pedido uk-card uk-card-default uk-card-body uk-position-sticky" style="top:90px">
                <h3 class="resumen-pedido__titulo">Tu pedido</h3>

                <ul class="resumen-pedido__lista">
                    <?php foreach($productos as $item): ?>
                    <li class="resumen-pedido__item">
                        <div class="uk-flex" style="gap:10px">
                            <img src="/img/productos/<?php echo s($item->imagen ?? 'default.webp'); ?>"
                                 width="40" height="40"
                                 style="object-fit:cover;border-radius:4px;flex-shrink:0"
                                 alt="">
                            <div class="uk-flex uk-flex-between uk-flex-1" style="align-items:flex-start">
                                <span class="uk-text-small">
                                    <?php echo s($item->nombre); ?>
                                    <br><small class="uk-text-muted">×<?php echo (int)$item->cantidad; ?></small>
                                </span>
                                <strong class="uk-text-small uk-text-nowrap">
                                    <?php echo formatear_precio($item->subtotal); ?>
                                </strong>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <hr>

                <div class="resumen-pedido__total uk-flex uk-flex-between">
                    <strong>Total</strong>
                    <strong class="resumen-pedido__precio-total">
                        <?php echo formatear_precio($total); ?>
                    </strong>
                </div>

                <a href="/carrito" class="uk-button uk-button-link uk-margin-small-top">
                    <span uk-icon="pencil"></span> Editar carrito
                </a>
            </div>
        </div>

    </div>
</div>
