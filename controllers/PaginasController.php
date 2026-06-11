<?php
namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use MVC\Router;

class PaginasController {

    // ── Home ───────────────────────────────────────────────
    public static function index(Router $router) {
        $destacados = Producto::whereArray(['destacado' => 1]);
        $categorias = Categoria::all('ASC');
        $recientes  = Producto::get(8);

        foreach (array_merge($destacados, $recientes) as $producto) {
            $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
            if ($producto->subcategoria) {
                $producto->categoria = Categoria::find($producto->subcategoria->categoria_id);
            }
        }

        $router->render('paginas/index', [
            'titulo'     => 'Tienda de Hardware',
            'destacados' => $destacados,
            'categorias' => $categorias,
            'recientes'  => $recientes
        ]);
    }

    // ── Categoría: /categoria-producto/categoria?categoria=... ──
    public static function categoria(Router $router) {
        $slug = s($_GET['categoria'] ?? '');

        if (!$slug) {
            header('Location: /');
            exit;
        }

        $categoria = Categoria::porSlug($slug);
        if (!$categoria) {
            header('Location: /404');
            exit;
        }

        $productos     = Producto::porCategoria($categoria->id);
        $subcategorias = Subcategoria::porCategoria($categoria->id);

        // Nombre de subcategoría para el overline de cada tarjeta
        $subs_por_id = [];
        foreach ($subcategorias as $sub) $subs_por_id[$sub->id] = $sub;
        foreach ($productos as $producto) {
            $producto->subcategoria = $subs_por_id[$producto->subcategoria_id] ?? null;
        }

        $router->render('tienda/categoria', [
            'meta_descripcion' => $categoria->descripcion ?: "Comprá {$categoria->nombre} al mejor precio en Tienda Hardware",
            'titulo'        => $categoria->nombre,
            'categoria'     => $categoria,
            'subcategorias' => $subcategorias,
            'productos'     => $productos
        ]);
    }

    // ── Subcategoría: /categoria-producto/subcategoria?categoria=...&subcategoria=... ──
    public static function subcategoria(Router $router) {
        $slug_categoria    = s($_GET['categoria']    ?? '');
        $slug_subcategoria = s($_GET['subcategoria'] ?? '');

        if (!$slug_categoria || !$slug_subcategoria) {
            header('Location: /');
            exit;
        }

        $categoria    = Categoria::porSlug($slug_categoria);
        $subcategoria = Subcategoria::porSlug($slug_subcategoria);

        if (!$categoria || !$subcategoria) {
            header('Location: /404');
            exit;
        }

        if ($subcategoria->categoria_id != $categoria->id) {
            header('Location: /404');
            exit;
        }

        $productos     = Producto::porSubcategoria($subcategoria->id);
        $subcategorias = Subcategoria::porCategoria($categoria->id);

        $router->render('tienda/subcategoria', [
            'meta_descripcion' => "{$subcategoria->nombre} en {$categoria->nombre} — Tienda Hardware",
            'titulo'        => $subcategoria->nombre . ' — ' . $categoria->nombre,
            'categoria'     => $categoria,
            'subcategoria'  => $subcategoria,
            'subcategorias' => $subcategorias,
            'productos'     => $productos
        ]);
    }

    // ── Detalle de producto ────────────────────────────────
    public static function producto(Router $router) {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /');
            exit;
        }

        $producto = Producto::find($id);
        if (!$producto) {
            header('Location: /404');
            exit;
        }

        $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
        $producto->categoria    = $producto->subcategoria
            ? Categoria::find($producto->subcategoria->categoria_id)
            : null;

        $relacionados = Producto::consultarSQL(
            "SELECT * FROM productos WHERE subcategoria_id = {$producto->subcategoria_id} AND id != {$id} LIMIT 4"
        );

        $router->render('tienda/producto', [
            'meta_descripcion' => mb_substr($producto->nombre . ' — ' . $producto->descripcion, 0, 155),
            'titulo'       => $producto->nombre,
            'producto'     => $producto,
            'relacionados' => $relacionados
        ]);
    }

    // ── Sobre ──────────────────────────────────────────────
    public static function sobre(Router $router) {
        $router->render('paginas/sobre', ['titulo' => 'Sobre nosotros']);
    }

    // ── Garantía ───────────────────────────────────────────
    public static function garantia(Router $router) {
        $router->render('paginas/garantia', ['titulo' => 'Garantía']);
    }

    // ── Contacto ───────────────────────────────────────────
    public static function contacto(Router $router) {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                $alertas['error'][] = 'La sesión expiró, intentá de nuevo';
                $router->render('paginas/contacto', ['titulo' => 'Contacto', 'alertas' => $alertas]);
                return;
            }
            $nombre  = s($_POST['nombre']  ?? '');
            $email   = s($_POST['email']   ?? '');
            $mensaje = s($_POST['mensaje'] ?? '');

            if (!$nombre)  $alertas['error'][] = 'El nombre es obligatorio';
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
                           $alertas['error'][] = 'El email no es válido';
            if (!$mensaje) $alertas['error'][] = 'El mensaje es obligatorio';

            if (empty($alertas)) {
                $alertas['exito'][] = '¡Mensaje enviado! Te responderemos pronto';
            }
        }

        $router->render('paginas/contacto', [
            'titulo'  => 'Contacto',
            'alertas' => $alertas
        ]);
    }

    // ── 404 ────────────────────────────────────────────────
    public static function error(Router $router) {
        http_response_code(404);
        $router->render('paginas/error', ['titulo' => 'Página no encontrada']);
    }
}