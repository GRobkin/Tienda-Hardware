<?php
/**
 * Vista: paginas/contacto.php
 * Variables: $alertas
 */
?>

<div class="contenedor contenedor--angosto">

    <header class="page-header">
        <h1 class="page-header__titulo">Contacto</h1>
        <p class="page-header__meta">¿Tenés una consulta? Escribinos y te respondemos a la brevedad.</p>
    </header>

    <div class="contacto-layout seccion">

        <section>
            <?php include __DIR__ . '/../parciales/alertas.php'; ?>

            <form method="POST" action="/contacto" class="form-apilado" novalidate>
                <?= csrf_field() ?>

                <div class="campo">
                    <label class="campo__label" for="nombre">Nombre</label>
                    <input class="campo__input"
                           id="nombre"
                           name="nombre"
                           type="text"
                           placeholder="Tu nombre"
                           autocomplete="name"
                           required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="email">Correo electrónico</label>
                    <input class="campo__input"
                           id="email"
                           name="email"
                           type="email"
                           placeholder="tu@correo.com"
                           autocomplete="email"
                           required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="mensaje">Mensaje</label>
                    <textarea class="campo__textarea"
                              id="mensaje"
                              name="mensaje"
                              placeholder="Contanos en qué te podemos ayudar"
                              required></textarea>
                </div>

                <button type="submit" class="boton boton--primario boton--lg">
                    Enviar mensaje
                </button>
            </form>
        </section>

        <aside class="contacto-info">
            <h2 class="resumen__titulo">Otras vías</h2>
            <ul class="contacto-info__lista">
                <li>
                    <strong>Local</strong>
                    <span>18 de Julio 1234, Montevideo, Uruguay</span>
                </li>
                <li>
                    <strong>Teléfono</strong>
                    <span>(+598) 2900 0000</span>
                </li>
                <li>
                    <strong>Email</strong>
                    <span>hola@tiendahardware.uy</span>
                </li>
                <li>
                    <strong>Horario</strong>
                    <span>Lunes a viernes de 9 a 19 h · Sábados de 9 a 13 h</span>
                </li>
            </ul>
        </aside>

    </div>

</div>
