<?php /** Vista: auth/mensaje.php — Variables: $demo_link */ ?>

<div class="auth">
    <div class="auth__card auth__card--mensaje">

        <div class="auth__icono-ok">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>

        <h1 class="auth__titulo">¡Cuenta creada!</h1>
        <p class="auth__subtitulo">
            Te enviamos un email con un enlace para confirmar tu cuenta.
            Hasta que la confirmes no vas a poder iniciar sesión.
        </p>

        <?php if (!empty($demo_link)): ?>
        <div class="alerta alerta--aviso">
            Modo demo (sin servidor de correo):
            <a href="<?= s($demo_link) ?>">confirmar la cuenta con este enlace</a>.
            También quedó una copia del email en la carpeta <code>/emails</code>.
        </div>
        <?php endif; ?>

        <div class="auth__mensaje-acciones">
            <a href="/login" class="auth__btn">Ir al login</a>
        </div>

    </div>
</div>
