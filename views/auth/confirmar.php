<?php /** Vista: auth/confirmar.php — Variables: $alertas */ ?>

<div class="auth">
    <div class="auth__card auth__card--mensaje">

        <?php
        $alertas = $alertas ?? [];
        $es_error = !empty($alertas['error']);
        ?>

        <div class="auth__icono-ok <?= $es_error ? 'auth__icono-ok--error' : '' ?>">
            <?php if ($es_error): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../parciales/alertas.php'; ?>

        <div class="auth__mensaje-acciones">
            <a href="/login" class="auth__btn">Ir al login</a>
        </div>

    </div>
</div>
