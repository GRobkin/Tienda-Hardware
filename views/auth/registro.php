<?php /** Vista: auth/registro.php — Variables: $alertas, $usuario */ ?>

<div class="auth">
    <div class="auth__card">

        <div class="auth__logo">
            <img src="/img/logo.png" alt="Tienda Hardware">
        </div>

        <h1 class="auth__titulo">Crear cuenta</h1>

        <?php include __DIR__ . '/../parciales/alertas.php'; ?>

        <form method="POST" action="/registro" class="auth__form" id="formRegistro" novalidate>

            <div class="auth__campo">
                <label class="auth__label" for="nombre">Nombre</label>
                <input class="auth__input"
                       id="nombre"
                       name="nombre"
                       type="text"
                       placeholder="Tu nombre"
                       value="<?= s($usuario->nombre ?? '') ?>"
                       autocomplete="given-name"
                       required>
                <span class="auth__error" id="errorNombre"></span>
            </div>

            <div class="auth__campo">
                <label class="auth__label" for="apellido">Apellido</label>
                <input class="auth__input"
                       id="apellido"
                       name="apellido"
                       type="text"
                       placeholder="Tu apellido"
                       value="<?= s($usuario->apellido ?? '') ?>"
                       autocomplete="family-name"
                       required>
                <span class="auth__error" id="errorApellido"></span>
            </div>

            <div class="auth__campo">
                <label class="auth__label" for="email">Correo electrónico</label>
                <input class="auth__input"
                       id="email"
                       name="email"
                       type="email"
                       placeholder="tu@correo.com"
                       value="<?= s($usuario->email ?? '') ?>"
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
                           placeholder="Mínimo 6 caracteres"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="auth__toggle-pass" id="togglePass" aria-label="Mostrar contraseña">
                        <i class="nav__icon nav__icon--eye" id="iconEye"></i>
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
                Crear cuenta
            </button>

        </form>

        <div class="auth__links">
            <a href="/login" class="auth__link auth__link--secundario">
                Ya tengo cuenta
            </a>
        </div>

    </div>
</div>
