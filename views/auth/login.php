<?php
/**
 * Vista: auth/login.php
 * Variables: $alertas
 */
?>

<div class="autenticacion uk-section">
    <div class="uk-container uk-container-small">
        <div class="uk-card uk-card-default uk-card-body uk-width-1-2@m uk-margin-auto">

            <h1 class="autenticacion__titulo uk-text-center">Iniciar sesión</h1>

            <?php include __DIR__ . '/../parciales/alertas.php'; ?>

            <form method="POST" action="/login" class="formulario-auth">

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email</label>
                    <input id="email" name="email" class="uk-input" type="email"
                           placeholder="tu@email.com" required autofocus>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Contraseña</label>
                    <input id="password" name="password" class="uk-input" type="password"
                           placeholder="••••••••" required>
                </div>

                <div class="uk-margin">
                    <button type="submit" class="uk-button uk-button-primary uk-width-1-1">
                        Ingresar
                    </button>
                </div>

                <div class="uk-text-center autenticacion__enlaces">
                    <a href="/olvide">¿Olvidaste tu contraseña?</a>
                    <span class="uk-margin-small-left uk-margin-small-right">·</span>
                    <a href="/registro">Crear cuenta</a>
                </div>
            </form>
        </div>
    </div>
</div>
