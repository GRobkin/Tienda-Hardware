<?php /** Vista: auth/restablecer.php — Variables: $token_valido, $alertas */ ?>
<div class="autenticacion uk-section">
    <div class="uk-container uk-container-small">
        <div class="uk-card uk-card-default uk-card-body uk-width-1-2@m uk-margin-auto">
            <h1 class="autenticacion__titulo uk-text-center">Nueva contraseña</h1>
            <?php include __DIR__ . '/../parciales/alertas.php'; ?>
            <?php if($token_valido ?? false): ?>
            <form method="POST" action="/restablecer">
                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Nueva contraseña</label>
                    <input id="password" name="password" class="uk-input" type="password"
                           placeholder="Mínimo 6 caracteres" required>
                </div>
                <button type="submit" class="uk-button uk-button-primary uk-width-1-1">Guardar contraseña</button>
            </form>
            <?php else: ?>
            <p class="uk-text-center uk-text-danger">El enlace no es válido o ya fue utilizado.</p>
            <a href="/olvide" class="uk-button uk-button-default uk-width-1-1 uk-margin-top">Solicitar nuevo enlace</a>
            <?php endif; ?>
        </div>
    </div>
</div>
