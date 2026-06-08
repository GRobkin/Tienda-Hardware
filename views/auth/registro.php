<?php
/**
 * Vista: auth/registro.php
 * Variables: $usuario, $alertas
 */
?>

<div class="autenticacion uk-section">
    <div class="uk-container uk-container-small">
        <div class="uk-card uk-card-default uk-card-body uk-width-1-2@m uk-margin-auto">
            <h1 class="autenticacion__titulo uk-text-center">Crear cuenta</h1>

            <?php include __DIR__ . '/../parciales/alertas.php'; ?>

            <form method="POST" action="/registro" class="formulario-auth">

                <div class="uk-grid uk-child-width-1-2" uk-grid>
                    <div>
                        <label class="uk-form-label" for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" class="uk-input" type="text"
                               value="<?php echo s($usuario->nombre ?? ''); ?>" required>
                    </div>
                    <div>
                        <label class="uk-form-label" for="apellido">Apellido</label>
                        <input id="apellido" name="apellido" class="uk-input" type="text"
                               value="<?php echo s($usuario->apellido ?? ''); ?>" required>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email</label>
                    <input id="email" name="email" class="uk-input" type="email"
                           value="<?php echo s($usuario->email ?? ''); ?>" required>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Contraseña</label>
                    <input id="password" name="password" class="uk-input" type="password"
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password2">Repetir contraseña</label>
                    <input id="password2" name="password2" class="uk-input" type="password" required>
                </div>

                <button type="submit" class="uk-button uk-button-primary uk-width-1-1">
                    Registrarse
                </button>

                <p class="uk-text-center uk-margin-top">
                    ¿Ya tenés cuenta? <a href="/login">Iniciá sesión</a>
                </p>
            </form>
        </div>
    </div>
</div>
