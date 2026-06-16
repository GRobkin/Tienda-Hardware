<?php

// Escapa HTML para evitar XSS
function s($html): string
{
    return htmlspecialchars($html ?? '');
}

// Debug rápido (solo en desarrollo)
function dd($variable): void
{
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Verifica si hay una sesión activa (usuario logueado)
function is_auth(): bool
{
    if (!isset($_SESSION)) session_start();
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

// Verifica si el usuario es admin
function is_admin(): bool
{
    if (!isset($_SESSION)) session_start();
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

// Formatea precio en dólares (formato uruguayo: US$ 1.234,56)
function formatear_precio($precio): string
{
    return 'US$ ' . number_format((float)$precio, 2, ',', '.');
}

// Imágenes de producto

// Mapa subcategoria_id => slug (una sola consulta por request)
function _subcategorias_slug_por_id(): array
{
    static $mapa = null;
    if ($mapa === null) {
        $mapa = [];
        try {
            foreach (\Model\Subcategoria::all('ASC') as $s) {
                $mapa[(int)$s->id] = $s->slug;
            }
        } catch (\Throwable $e) {
            $mapa = [];
        }
    }
    return $mapa;
}

// Slug de subcategoría => nombre del ícono SVG del catálogo
function icono_catalogo_por_slug($slug): string
{
    $map = [
        'cpu' => 'cpu',
        'gpu' => 'gpu',
        'ram' => 'ram',
        'nvme' => 'ram',
        'ssd' => 'almacenamiento',
        'hdd' => 'almacenamiento',
        'discos-externos' => 'almacenamiento',
        'placas-madre' => 'placa-madre',
        'placas-red' => 'placa-madre',
        'fuentes' => 'fuente',
        'teclados' => 'teclado',
        'mouse' => 'mouse',
        'auriculares' => 'auriculares',
        'webcam' => 'webcam',
        'pad-mouse' => 'pad',
        'monitores-gaming' => 'monitor',
        'monitores-profesional' => 'monitor',
        'monitores-ultrawide' => 'monitor',
        'cables-video' => 'cable',
        'cables-usb' => 'cable',
        'cables-sata' => 'cable',
        'cables-red' => 'cable',
        'adaptadores' => 'cable',
        'routers' => 'red',
        'switches' => 'red',
        'pendrives' => 'usb',
        'tarjetas-sd' => 'sd',
        'atx' => 'gabinete',
        'micro-atx' => 'gabinete',
        'mini-itx' => 'gabinete',
        'limpieza' => 'servicio',
        'formateo' => 'servicio',
        'armado-pc' => 'servicio',
        'diagnostico' => 'servicio',
    ];
    return $map[$slug] ?? 'default';
}

/**
 * Devuelve la URL de la imagen de un producto:
 *  1) la foto real subida desde el admin, si existe el archivo;
 *  2) si no, el ícono SVG que corresponde a su tipo de producto.
 */
function imagen_producto($producto): string
{
    $imagen = $producto->imagen ?? '';
    if ($imagen && $imagen !== 'default.webp') {
        $ruta = __DIR__ . '/../public/img/productos/' . $imagen;
        if (is_file($ruta)) {
            return '/img/productos/' . $imagen;
        }
    }
    $slug = _subcategorias_slug_por_id()[(int)($producto->subcategoria_id ?? 0)] ?? '';
    return '/img/catalogo/' . icono_catalogo_por_slug($slug) . '.svg';
}

// Devuelve la cantidad total de items en el carrito
function total_carrito(): int
{
    if (!isset($_SESSION)) session_start();
    return array_sum($_SESSION['carrito'] ?? []);
}

// Resalta la página activa en el menú
function pagina_activa($ruta): string
{
    $actual = $_SERVER['PATH_INFO'] ?? '/';
    return str_starts_with($actual, $ruta) ? 'activo' : '';
}

// CSRF

// Token CSRF de la sesión (se crea una sola vez)
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

// Campo oculto para formularios POST
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Verifica el token recibido por POST
function csrf_check(): bool
{
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

// Mensajes flash (sobreviven a un redirect)

function flash($tipo, $mensaje): void
{
    $_SESSION['flash'][$tipo][] = $mensaje;
}

function obtener_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}
