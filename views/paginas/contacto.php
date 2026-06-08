<?php
/**
 * Vista: paginas/contacto.php
 * Variables: $alertas
 */
?>

<section class="cabecera-seccion uk-section uk-section-muted uk-padding-small">
    <div class="uk-container">
        <ul class="uk-breadcrumb"><li><a href="/">Inicio</a></li><li>Contacto</li></ul>
        <h1 class="cabecera-seccion__titulo">Contacto</h1>
    </div>
</section>

<div class="uk-container uk-section">
    <div class="uk-grid uk-grid-large" uk-grid>

        <div class="uk-width-2-3@m">
            <?php include __DIR__ . '/../parciales/alertas.php'; ?>

            <form method="POST" action="/contacto" class="formulario-contacto">
                <div class="uk-margin">
                    <label class="uk-form-label" for="nombre">Nombre *</label>
                    <input
                        id="nombre"
                        name="nombre"
                        class="uk-input"
                        type="text"
                        placeholder="Tu nombre"
                        required
                    >
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email *</label>
                    <input
                        id="email"
                        name="email"
                        class="uk-input"
                        type="email"
                        placeholder="tu@email.com"
                        required
                    >
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="mensaje">Mensaje *</label>
                    <textarea
                        id="mensaje"
                        name="mensaje"
                        class="uk-textarea"
                        rows="6"
                        placeholder="¿En qué podemos ayudarte?"
                        required
                    ></textarea>
                </div>

                <button type="submit" class="uk-button uk-button-primary">
                    Enviar mensaje
                </button>
            </form>
        </div>

        <div class="uk-width-1-3@m">
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title">Información</h3>
                <ul class="lista-contacto">
                    <li>
                        <span uk-icon="location"></span>
                        Dirección de la tienda
                    </li>
                    <li>
                        <span uk-icon="receiver"></span>
                        +54 9 000 000-0000
                    </li>
                    <li>
                        <span uk-icon="mail"></span>
                        contacto@ferreteria.com
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
