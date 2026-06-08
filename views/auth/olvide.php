<?php /** Vista: auth/olvide.php */ ?>
<div class="autenticacion uk-section">
    <div class="uk-container uk-container-small">
        <div class="uk-card uk-card-default uk-card-body uk-width-1-2@m uk-margin-auto">
            <h1 class="autenticacion__titulo uk-text-center">Recuperar contraseña</h1>
            <?php include __DIR__ . '/../parciales/alertas.php'; ?>
            <form method="POST" action="/olvide">
                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email de tu cuenta</label>
                    <input id="email" name="email" class="uk-input" type="email" required>
                </div>
                <button type="submit" class="uk-button uk-button-primary uk-width-1-1">Enviar enlace</button>
                <p class="uk-text-center uk-margin-top"><a href="/login">← Volver al login</a></p>
            </form>
        </div>
    </div>
</div>
