<?php
/**
 * Vista: usuario/modificar.php
 * Variables: $usuario, $alertas
 */
?>

<div class="contenedor contenedor--angosto">

    <header class="page-header">
        <p class="page-header__overline">Mi cuenta</p>
        <h1 class="page-header__titulo">Modificar mis datos</h1>
    </header>

    <?php include __DIR__ . '/../parciales/alertas.php'; ?>

    <div class="cuenta-grid seccion">

        <section class="panel">
            <h2 class="resumen__titulo">Datos personales</h2>

            <form method="POST" action="/cuenta/modificar" class="form-apilado">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="perfil">

                <div class="campo">
                    <label class="campo__label" for="nombre">Nombre</label>
                    <input class="campo__input" id="nombre" name="nombre" type="text"
                           value="<?= s($usuario->nombre) ?>" autocomplete="given-name" required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="apellido">Apellido</label>
                    <input class="campo__input" id="apellido" name="apellido" type="text"
                           value="<?= s($usuario->apellido) ?>" autocomplete="family-name" required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="email">Correo electrónico</label>
                    <input class="campo__input" id="email" name="email" type="email"
                           value="<?= s($usuario->email) ?>" autocomplete="email" required>
                </div>

                <button type="submit" class="boton boton--primario">Guardar cambios</button>
            </form>
        </section>

        <section class="panel">
            <h2 class="resumen__titulo">Cambiar contraseña</h2>

            <form method="POST" action="/cuenta/modificar" class="form-apilado">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="password">

                <div class="campo">
                    <label class="campo__label" for="password_actual">Contraseña actual</label>
                    <input class="campo__input" id="password_actual" name="password_actual"
                           type="password" autocomplete="current-password" required>
                </div>

                <div class="campo">
                    <label class="campo__label" for="password_nuevo">Nueva contraseña</label>
                    <input class="campo__input" id="password_nuevo" name="password_nuevo"
                           type="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                </div>

                <button type="submit" class="boton boton--secundario">Cambiar contraseña</button>
            </form>
        </section>

    </div>

    <p><a class="seccion__link" href="/cuenta">← Volver a mi cuenta</a></p>

</div>
