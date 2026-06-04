<?php

// Escapa HTML para evitar XSS
function s($html) : string {
    return htmlspecialchars($html ?? '');
}

// Debug 
function dd($variable) : void {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Verifica si hay una sesión activa (usuario logueado)
function is_auth() : bool {
    if(!isset($_SESSION)) session_start();
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

// Verifica si el usuario es admin
function is_admin() : bool {
    if(!isset($_SESSION)) session_start();
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

// Formatea precio en moneda local
function formatear_precio($precio) : string {
    return '$' . number_format((float)$precio, 2, ',', '.');
}

// Devuelve la cantidad total de items en el carrito
function total_carrito() : int {
    if(!isset($_SESSION)) session_start();
    return array_sum($_SESSION['carrito'] ?? []);
}

// Resalta la página activa en el menú
function pagina_activa($ruta) : string {
    $actual = $_SERVER['PATH_INFO'] ?? '/';
    return str_starts_with($actual, $ruta) ? 'activo' : '';
}
