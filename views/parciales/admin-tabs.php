<?php
// Lee la ruta directamente: este parcial se incluye dentro de Router::render(),
// así que una variable local jamás sería visible vía "global".
if (!function_exists('admin_tab_activo')) {
    function admin_tab_activo($prefijo) {
        $ruta = $_SERVER['PATH_INFO'] ?? '/';
        return $ruta === $prefijo || str_starts_with($ruta, $prefijo . '/') ? 'admin-tabs__tab--activo' : '';
    }
}
?>

<div class="admin-tabs">
    <div class="admin-tabs__contenedor">
        <a href="/admin/dashboard"
           class="admin-tabs__tab <?= admin_tab_activo('/admin/dashboard') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>
        <a href="/admin/productos"
           class="admin-tabs__tab <?= admin_tab_activo('/admin/productos') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            Productos
        </a>
        <a href="/admin/ordenes"
           class="admin-tabs__tab <?= admin_tab_activo('/admin/ordenes') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            Órdenes
        </a>
        <a href="/admin/usuarios"
           class="admin-tabs__tab <?= admin_tab_activo('/admin/usuarios') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Usuarios
        </a>
    </div>
</div>
