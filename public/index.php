<?php

// Servir archivos estáticos (css, js, imágenes)
$uri = $_SERVER['REQUEST_URI'];
$archivo = __DIR__ . parse_url($uri, PHP_URL_PATH);

if (is_file($archivo)) {
    return false; // PHP lo sirve directamente
}

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AuthController;
use Controllers\PaginasController;
use Controllers\CarritoController;
use Controllers\OrdenController;
use Controllers\AdminController;
use Controllers\BuscadorController;
use Controllers\UsuarioController;

$router = new Router();



// ── Área pública ──────────────────────────────────────────
$router->get('/',         [PaginasController::class, 'index']);
$router->get('/sobre',    [PaginasController::class, 'sobre']);
$router->get('/contacto', [PaginasController::class, 'contacto']);
$router->get('/garantia', [PaginasController::class, 'garantia']);
$router->post('/contacto',[PaginasController::class, 'contacto']);
$router->get('/404',      [PaginasController::class, 'error']);

// ── Producto detalle ──────────────────────────────────────
$router->get('/producto', [PaginasController::class, 'producto']);

// ── Navegación por categoría y subcategoría ───────────────
$router->get('/categoria-producto/categoria',    [PaginasController::class, 'categoria']);
$router->get('/categoria-producto/subcategoria', [PaginasController::class, 'subcategoria']);

// ── Buscador (JSON, usado por la barra de búsqueda) ───────
$router->get('/buscar', [BuscadorController::class, 'buscar']);

// ── Autenticación ─────────────────────────────────────────
$router->get('/login',        [AuthController::class, 'login']);
$router->post('/login',       [AuthController::class, 'login']);
$router->post('/logout',      [AuthController::class, 'logout']);
$router->get('/registro',     [AuthController::class, 'registro']);
$router->post('/registro',    [AuthController::class, 'registro']);
$router->get('/confirmar',    [AuthController::class, 'confirmar']);
$router->get('/olvide',       [AuthController::class, 'olvide']);
$router->post('/olvide',      [AuthController::class, 'olvide']);
$router->get('/restablecer',  [AuthController::class, 'restablecer']);
$router->post('/restablecer', [AuthController::class, 'restablecer']);
$router->get('/mensaje',      [AuthController::class, 'mensaje']);

// ── Carrito ───────────────────────────────────────────────
$router->get('/carrito',             [CarritoController::class, 'index']);
$router->post('/carrito/agregar',    [CarritoController::class, 'agregar']);
$router->post('/carrito/actualizar', [CarritoController::class, 'actualizar']);
$router->post('/carrito/eliminar',   [CarritoController::class, 'eliminar']);
$router->post('/carrito/vaciar',     [CarritoController::class, 'vaciar']);

// ── Órdenes ───────────────────────────────────────────────
$router->get('/checkout',           [OrdenController::class, 'checkout']);
$router->post('/checkout',          [OrdenController::class, 'checkout']);
$router->get('/orden/confirmacion', [OrdenController::class, 'confirmacion']);
$router->get('/mis-pedidos',        [OrdenController::class, 'misPedidos']);

// ── Mi cuenta ─────────────────────────────────────────────
$router->get('/cuenta',            [UsuarioController::class, 'dashboard']);
$router->get('/cuenta/modificar',  [UsuarioController::class, 'modificar']);
$router->post('/cuenta/modificar', [UsuarioController::class, 'modificar']);

// ── Admin: dashboard ──────────────────────────────────────
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);

// ── Admin: productos ──────────────────────────────────────
$router->get('/admin/productos',           [AdminController::class, 'productos']);
$router->get('/admin/productos/crear',     [AdminController::class, 'crearProducto']);
$router->post('/admin/productos/crear',    [AdminController::class, 'crearProducto']);
$router->get('/admin/productos/editar',    [AdminController::class, 'editarProducto']);
$router->post('/admin/productos/editar',   [AdminController::class, 'editarProducto']);
$router->post('/admin/productos/eliminar', [AdminController::class, 'eliminarProducto']);

// ── Admin: órdenes y usuarios ─────────────────────────────
$router->get('/admin/ordenes',        [AdminController::class, 'ordenes']);
$router->get('/admin/ordenes/crear',  [AdminController::class, 'crearOrden']);
$router->post('/admin/ordenes/crear', [AdminController::class, 'crearOrden']);
$router->get('/admin/usuarios',       [AdminController::class, 'usuarios']);

$router->comprobarRutas();
