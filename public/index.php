<?php
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AuthController;
use Controllers\PaginasController;
use Controllers\CarritoController;
use Controllers\OrdenController;
use Controllers\AdminController;
use Controllers\BuscadorController;

$router = new Router();

// ── Área pública ──────────────────────────────────────────
$router->get('/',         [PaginasController::class, 'index']);
$router->get('/sobre',    [PaginasController::class, 'sobre']);
$router->get('/contacto', [PaginasController::class, 'contacto']);
$router->post('/contacto',[PaginasController::class, 'contacto']);
$router->get('/404',      [PaginasController::class, 'error']);
$router->get('/buscar', [BuscadorController::class, 'buscar']);

// ── Producto detalle ──────────────────────────────────────
$router->get('/producto', [PaginasController::class, 'producto']);

// ── Navegación por categoría y subcategoría ───────────────
// /categoria-producto/componentes
// /categoria-producto/componentes/ssd
$router->get('/categoria-producto/categoria',    [PaginasController::class, 'categoria']);
$router->get('/categoria-producto/subcategoria', [PaginasController::class, 'subcategoria']);

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

// ── Órdenes ───────────────────────────────────────────────
$router->get('/checkout',           [OrdenController::class, 'checkout']);
$router->post('/checkout',          [OrdenController::class, 'checkout']);
$router->get('/orden/confirmacion', [OrdenController::class, 'confirmacion']);
$router->get('/mis-pedidos',        [OrdenController::class, 'misPedidos']);

// ── Admin: dashboard ──────────────────────────────────────
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);

// ── Admin: productos ──────────────────────────────────────
$router->get('/admin/productos',             [AdminController::class, 'productos']);
$router->get('/admin/productos/crear',       [AdminController::class, 'crearProducto']);
$router->post('/admin/productos/crear',      [AdminController::class, 'crearProducto']);
$router->get('/admin/productos/editar',      [AdminController::class, 'editarProducto']);
$router->post('/admin/productos/editar',     [AdminController::class, 'editarProducto']);
$router->post('/admin/productos/eliminar',   [AdminController::class, 'eliminarProducto']);

// ── Admin: categorías ─────────────────────────────────────
$router->get('/admin/categorias',            [AdminController::class, 'categorias']);
$router->get('/admin/categorias/crear',      [AdminController::class, 'crearCategoria']);
$router->post('/admin/categorias/crear',     [AdminController::class, 'crearCategoria']);
$router->get('/admin/categorias/editar',     [AdminController::class, 'editarCategoria']);
$router->post('/admin/categorias/editar',    [AdminController::class, 'editarCategoria']);
$router->post('/admin/categorias/eliminar',  [AdminController::class, 'eliminarCategoria']);

// ── Admin: subcategorías ──────────────────────────────────
$router->get('/admin/subcategorias',           [AdminController::class, 'subcategorias']);
$router->get('/admin/subcategorias/crear',     [AdminController::class, 'crearSubcategoria']);
$router->post('/admin/subcategorias/crear',    [AdminController::class, 'crearSubcategoria']);
$router->get('/admin/subcategorias/editar',    [AdminController::class, 'editarSubcategoria']);
$router->post('/admin/subcategorias/editar',   [AdminController::class, 'editarSubcategoria']);
$router->post('/admin/subcategorias/eliminar', [AdminController::class, 'eliminarSubcategoria']);

// ── Admin: órdenes y usuarios ─────────────────────────────
$router->get('/admin/ordenes',  [AdminController::class, 'ordenes']);
$router->get('/admin/usuarios', [AdminController::class, 'usuarios']);

$router->comprobarRutas();
