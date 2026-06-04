<?php
namespace Controllers;

use Model\Producto;
use Model\Categoria;
use MVC\Router;

class PaginasController {

    // ── Home: productos destacados + listado general ───────
    public static function index(Router $router) {
        $destacados  = Producto::whereArray(['destacado' => 1]);
        $categorias  = Categoria::all('ASC');
        $recientes   = Producto::get(8);

        $router->render('paginas/index', [
            'titulo'     => 'Tienda de Hardware',
            'destacados' => $destacados,
            'categorias' => $categorias,
            'recientes'  => $recientes
        ]);
    }

    // ── Catálogo con búsqueda y filtro por categoría ───────
    public static function catalogo(Router $router) {
        $categorias  = Categoria::all('ASC');
        $busqueda    = s($_GET['busqueda']    ?? '');
        $categoria_id = filter_var($_GET['categoria'] ?? 0, FILTER_VALIDATE_INT);

        // Construir consulta dinámica
        $query = "SELECT * FROM productos WHERE 1=1";
        if($busqueda) {
            $busqueda_safe = self::$db_escape($busqueda);
            $query .= " AND (nombre LIKE '%{$busqueda}%' OR descripcion LIKE '%{$busqueda}%')";
        }
        if($categoria_id) {
            $query .= " AND categoria_id = {$categoria_id}";
        }
        $query .= " ORDER BY id DESC";

        $productos = Producto::consultarSQL($query);

        $router->render('paginas/catalogo', [
            'titulo'      => 'Catálogo',
            'productos'   => $productos,
            'categorias'  => $categorias,
            'busqueda'    => $busqueda,
            'categoria_id' => $categoria_id
        ]);
    }

    // ── Detalle de un producto ─────────────────────────────
    public static function producto(Router $router) {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if(!$id) { header('Location: /catalogo'); exit; }

        $producto = Producto::find($id);
        if(!$producto) { header('Location: /catalogo'); exit; }

        $producto->categoria = Categoria::find($producto->categoria_id);

        // Productos relacionados (misma categoría)
        $relacionados_query = "SELECT * FROM productos WHERE categoria_id = {$producto->categoria_id} AND id != {$id} LIMIT 4";
        $relacionados = Producto::consultarSQL($relacionados_query);

        $router->render('paginas/producto', [
            'titulo'      => $producto->nombre,
            'producto'    => $producto,
            'relacionados' => $relacionados
        ]);
    }

    // ── Sobre nosotros ─────────────────────────────────────
    public static function sobre(Router $router) {
        $router->render('paginas/sobre', [
            'titulo' => 'Sobre nosotros'
        ]);
    }

    // ── Contacto ───────────────────────────────────────────
    public static function contacto(Router $router) {
        $alertas = [];
        $enviado = false;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = s($_POST['nombre']  ?? '');
            $email   = s($_POST['email']   ?? '');
            $mensaje = s($_POST['mensaje'] ?? '');

            if(!$nombre)  $alertas['error'][] = 'El nombre es obligatorio';
            if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
                          $alertas['error'][] = 'El email no es válido';
            if(!$mensaje) $alertas['error'][] = 'El mensaje es obligatorio';

            if(empty($alertas)) {
                // Aquí iría el envío de email real
                $enviado = true;
                $alertas['exito'][] = '¡Mensaje enviado! Te responderemos pronto';
            }
        }

        $router->render('paginas/contacto', [
            'titulo'  => 'Contacto',
            'alertas' => $alertas,
            'enviado' => $enviado
        ]);
    }

    // ── 404 ────────────────────────────────────────────────
    public static function error(Router $router) {
        $router->render('paginas/error', [
            'titulo' => 'Página no encontrada'
        ]);
    }
}
