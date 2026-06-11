<?php /** Vista: auth/restablecer.php — Variables: $alertas, $token_valido */ ?>

<div class="auth">
    <div class="auth__card">

        <div class="auth__logo">
            <img src="/img/logo.png" alt="Tienda Hardware">
        </div>

        <h1 class="auth__titulo">Nueva contraseña</h1>

        <?php include __DIR__ . '/../parciales/alertas.php'; ?>

        <?php if ($token_valido ?? false): ?>

        <form method="POST" action="/restablecer?token=<?= s($_GET['token'] ?? '') ?>" class="auth__form" id="formRestablecer" novalidate>
            <?= csrf_field() ?>

            <div class="auth__campo">
                <label class="auth__label" for="password">Nueva contraseña</label>
                <div class="auth__input-wrap">
                    <input class="auth__input"
                           id="password"
                           name="password"
                           type="password"
                           placeholder="Mínimo 6 caracteres"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="auth__toggle-pass" id="togglePass" aria-label="Mostrar contraseña">
                        <i class="nav__icon nav__icon--eye"></i>
                    </button>
                </div>
                <span class="auth__error" id="errorPass"></span>
            </div>

            <div class="auth__campo">
                <label class="auth__label" for="password2">Repetir contraseña</label>
                <div class="auth__input-wrap">
                    <input class="auth__input"
                           id="password2"
                           name="password2"
                           type="password"
                           placeholder="Repetí tu contraseña"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="auth__toggle-pass" id="togglePass2" aria-label="Mostrar contraseña">
                        <i class="nav__icon nav__icon--eye"></i>
                    </button>
                </div>
                <span class="auth__error" id="errorPass2"></span>
            </div>

            <button type="submit" class="auth__btn">
                Guardar contraseña
            </button>

        </form>

        <?php else: ?>

        <div class="auth__token-invalido">
            <p>El enlace no es válido o ya fue utilizado.</p>
            <a href="/olvide" class="auth__btn auth__btn--outline">
                Solicitar nuevo enlace
            </a>
        </div>

        <?php endif; ?>

        <div class="auth__links">
            <a href="/login" class="auth__link">← Volver al login</a>
        </div>

    </div>
</div>
