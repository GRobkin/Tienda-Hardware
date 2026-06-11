<?php /** Vista: auth/olvide.php — Variables: $alertas */ ?>

<div class="auth">
    <div class="auth__card">

        <div class="auth__logo">
            <img src="/img/logo.png" alt="Tienda Hardware">
        </div>

        <h1 class="auth__titulo">Recuperar contraseña</h1>
        <p class="auth__subtitulo">
            Ingresá tu correo y te enviamos un enlace para restablecer tu contraseña.
        </p>

        <?php include __DIR__ . '/../parciales/alertas.php'; ?>

        <?php if (!empty($demo_link)): ?>
        <div class="alerta alerta--aviso">
            Modo demo (sin servidor de correo):
            <a href="<?= s($demo_link) ?>">abrir el enlace de restablecimiento</a>.
            También quedó una copia en la carpeta <code>/emails</code>.
        </div>
        <?php endif; ?>

        <form method="POST" action="/olvide" class="auth__form" id="formOlvide" novalidate>
            <?= csrf_field() ?>

            <div class="auth__campo">
                <label class="auth__label" for="email">Correo electrónico</label>
                <input class="auth__input"
                       id="email"
                       name="email"
                       type="email"
                       placeholder="tu@correo.com"
                       autocomplete="email"
                       required>
                <span class="auth__error" id="errorEmail"></span>
            </div>

            <button type="submit" class="auth__btn">
                Enviar enlace
            </button>

        </form>

        <div class="auth__links">
            <a href="/login" class="auth__link">
                ← Volver al login
            </a>
        </div>

    </div>
</div>
