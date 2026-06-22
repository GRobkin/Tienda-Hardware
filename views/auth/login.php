<?php /** Vista: auth/login.php — Variables: $alertas */ ?>

<div class="auth">
    <div class="auth__card">

        <div class="auth__logo">
            <img src="/img/logo.png" alt="Tienda Hardware">
        </div>

        <h1 class="auth__titulo">Iniciar sesión</h1>

        <?php include __DIR__ . '/../parciales/alertas.php'; ?>

        <form method="POST" action="/login" class="auth__form" id="formLogin" novalidate>
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

            <div class="auth__campo">
                <label class="auth__label" for="password">Contraseña</label>
                <div class="auth__input-wrap">
                    <input class="auth__input"
                           id="password"
                           name="password"
                           type="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="auth__toggle-pass" id="togglePass" aria-label="Mostrar contraseña">
                        <i class="nav__icon nav__icon--eye" id="iconEye"></i>
                    </button>
                </div>
                <span class="auth__error" id="errorPass"></span>
            </div>

            <button type="submit" class="auth__btn">
                Iniciar sesión
            </button>

        </form>

        <div class="auth__links">
            <a href="/registro" class="auth__link auth__link--secundario">
                Crear cuenta
            </a>
        </div>

    </div>
</div>