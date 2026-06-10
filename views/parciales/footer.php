<?php if ($es_auth): ?>

<footer class="footer-auth">
    <div class="footer-auth__container">

        <a href="/" class="footer-auth__logo">
            <img src="/img/logo.png" alt="Tienda Hardware">
        </a>

        <p class="footer-auth__desc">
            Tu tienda de hardware, componentes y tecnología de confianza.
        </p>

        <div class="footer-auth__links">
            <a href="/sobre">Sobre nosotros</a>
            <a href="/contacto">Contacto</a>
            <a href="/garantia">Garantías</a>
        </div>

        <div class="footer-auth__copy">
            &copy; <?= date('Y') ?> Tienda Hardware. Todos los derechos reservados.
        </div>

    </div>
</footer>

<?php else: ?>

<footer class="footer">
    <div class="footer__container">
        <div class="footer__grid">

            <div class="footer__col">
                <a href="/" class="footer__logo">
                    <img src="/img/logo.png" alt="Tienda Hardware">
                </a>
                <p class="footer__desc">
                    Tu tienda de hardware, componentes y tecnología de confianza.
                </p>
            </div>

            <div class="footer__col">
                <h4 class="footer__titulo">Categorías</h4>
                <ul class="footer__lista">
                    <?php
                    if (empty($categorias_nav)) {
                        try { $categorias_nav = \Model\Categoria::all('ASC'); }
                        catch (\Throwable $e) { $categorias_nav = []; }
                    }
                    foreach (array_slice($categorias_nav, 0, 6) as $cat):
                    ?>
                    <li>
                        <a href="/categoria-producto/categoria?categoria=<?= s($cat->slug) ?>">
                            <?= s($cat->nombre) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer__col">
                <h4 class="footer__titulo">Mi cuenta</h4>
                <ul class="footer__lista">
                    <?php if (is_auth()): ?>
                    <li><a href="/mis-pedidos">Mis pedidos</a></li>
                    <li><a href="/carrito">Carrito</a></li>
                    <?php else: ?>
                    <li><a href="/login">Iniciar sesión</a></li>
                    <li><a href="/registro">Crear cuenta</a></li>
                    <?php endif; ?>
                    <li><a href="/sobre">Sobre nosotros</a></li>
                    <li><a href="/contacto">Contacto</a></li>
                    <li><a href="/garantia">Garantías</a></li>
                </ul>
            </div>

        </div>

        <div class="footer__copy">
            <p>&copy; <?= date('Y') ?> Tienda Hardware. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<?php endif; ?>
