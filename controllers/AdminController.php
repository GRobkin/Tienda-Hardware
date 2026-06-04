<?php
namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use Model\Orden;
use Model\OrdenItem;
use Model\Usuario;
use MVC\Router;

class AdminController {

    // ── Dashboard ──────────────────────────────────────────
    public static function dashboard(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $total_productos = Producto::total();
        $total_ordenes   = Orden::total();
        $total_usuarios  = Usuario::total();
        $ordenes_recientes = Orden::get(5);

        foreach($ordenes_recientes as $orden) {
            $orden->usuario = Usuario::find($orden->usuario_id);
        }

        $router->render('admin/dashboard', [
            'titulo'             => 'Panel de administración',
            'total_productos'    => $total_productos,
            'total_ordenes'      => $total_ordenes,
            'total_usuarios'     => $total_usuarios,
            'ordenes_recientes'  => $ordenes_recientes
        ]);
    }

    // ══════════════════════════════════════════════════════
    // PRODUCTOS
    // ══════════════════════════════════════════════════════

    public static function productos(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if(!$pagina_actual || $pagina_actual < 1) { header('Location: /admin/productos?page=1'); exit; }

        $por_pagina    = 10;
        $total         = Producto::total();
        $offset        = ($pagina_actual - 1) * $por_pagina;
        $productos     = Producto::paginar($por_pagina, $offset);

        foreach($productos as $producto) {
            $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
            if($producto->subcategoria) {
                $producto->categoria = Categoria::find($producto->subcategoria->categoria_id);
            }
        }

        $router->render('admin/productos/index', [
            'titulo'        => 'Productos',
            'productos'     => $productos,
            'pagina_actual' => $pagina_actual,
            'total_paginas' => ceil($total / $por_pagina)
        ]);
    }

    public static function crearProducto(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $categorias = Categoria::all('ASC');
        $producto   = new Producto;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->sincronizar($_POST);

            if(!empty($_FILES['imagen']['tmp_name'])) {
                $carpeta = '../public/img/productos';
                if(!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                $extension    = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = md5(uniqid(rand(), true)) . '.' . $extension;
                if(move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . '/' . $nombre_imagen)) {
                    $producto->imagen = $nombre_imagen;
                }
            }

            $alertas = $producto->validar();
            if(empty($alertas)) {
                $resultado = $producto->guardar();
                if($resultado['resultado']) { header('Location: /admin/productos'); exit; }
            }
        }

        $router->render('admin/productos/crear', [
            'titulo'     => 'Nuevo producto',
            'producto'   => $producto,
            'categorias' => $categorias,
            'alertas'    => Producto::getAlertas()
        ]);
    }

    public static function editarProducto(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if(!$id) { header('Location: /admin/productos'); exit; }

        $producto = Producto::find($id);
        if(!$producto) { header('Location: /admin/productos'); exit; }

        $categorias             = Categoria::all('ASC');
        $producto->imagen_actual = $producto->imagen;

        // Subcategorías de la categoría actual (para el select)
        $subcategoria_actual = Subcategoria::find($producto->subcategoria_id);
        $subcategorias = $subcategoria_actual
            ? Subcategoria::porCategoria($subcategoria_actual->categoria_id)
            : [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto->sincronizar($_POST);

            if(!empty($_FILES['imagen']['tmp_name'])) {
                $carpeta = '../public/img/productos';
                if(!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                $extension    = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = md5(uniqid(rand(), true)) . '.' . $extension;
                if(move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . '/' . $nombre_imagen)) {
                    $producto->imagen = $nombre_imagen;
                }
            } else {
                $producto->imagen = $producto->imagen_actual;
            }

            $alertas = $producto->validar();
            if(empty($alertas)) {
                $producto->guardar();
                header('Location: /admin/productos');
                exit;
            }
        }

        $router->render('admin/productos/editar', [
            'titulo'        => 'Editar producto',
            'producto'      => $producto,
            'categorias'    => $categorias,
            'subcategorias' => $subcategorias,
            'alertas'       => Producto::getAlertas()
        ]);
    }

    public static function eliminarProducto() {
        if(!is_admin()) { header('Location: /login'); exit; }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $producto = Producto::find($id);
            if($producto) $producto->eliminar();
            header('Location: /admin/productos');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════
    // CATEGORÍAS
    // ══════════════════════════════════════════════════════

    public static function categorias(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $categorias = Categoria::all('ASC');
        $router->render('admin/categorias/index', [
            'titulo'     => 'Categorías',
            'categorias' => $categorias
        ]);
    }

    public static function crearCategoria(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $categoria = new Categoria;
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();
            if(empty($alertas)) {
                $resultado = $categoria->guardar();
                if($resultado['resultado']) { header('Location: /admin/categorias'); exit; }
            }
        }

        $router->render('admin/categorias/crear', [
            'titulo'    => 'Nueva categoría',
            'categoria' => $categoria,
            'alertas'   => Categoria::getAlertas()
        ]);
    }

    public static function editarCategoria(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if(!$id) { header('Location: /admin/categorias'); exit; }

        $categoria = Categoria::find($id);
        if(!$categoria) { header('Location: /admin/categorias'); exit; }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();
            if(empty($alertas)) {
                $categoria->guardar();
                header('Location: /admin/categorias');
                exit;
            }
        }

        $router->render('admin/categorias/editar', [
            'titulo'    => 'Editar categoría',
            'categoria' => $categoria,
            'alertas'   => Categoria::getAlertas()
        ]);
    }

    public static function eliminarCategoria() {
        if(!is_admin()) { header('Location: /login'); exit; }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $categoria = Categoria::find($id);
            if($categoria) $categoria->eliminar();
            header('Location: /admin/categorias');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════
    // SUBCATEGORÍAS
    // ══════════════════════════════════════════════════════

    public static function subcategorias(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $subcategorias = Subcategoria::all('ASC');
        foreach($subcategorias as $sub) {
            $sub->categoria = Categoria::find($sub->categoria_id);
        }

        $router->render('admin/subcategorias/index', [
            'titulo'        => 'Subcategorías',
            'subcategorias' => $subcategorias
        ]);
    }

    public static function crearSubcategoria(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $categorias   = Categoria::all('ASC');
        $subcategoria = new Subcategoria;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subcategoria->sincronizar($_POST);
            $alertas = $subcategoria->validar();
            if(empty($alertas)) {
                $resultado = $subcategoria->guardar();
                if($resultado['resultado']) { header('Location: /admin/subcategorias'); exit; }
            }
        }

        $router->render('admin/subcategorias/crear', [
            'titulo'       => 'Nueva subcategoría',
            'subcategoria' => $subcategoria,
            'categorias'   => $categorias,
            'alertas'      => Subcategoria::getAlertas()
        ]);
    }

    public static function editarSubcategoria(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if(!$id) { header('Location: /admin/subcategorias'); exit; }

        $subcategoria = Subcategoria::find($id);
        if(!$subcategoria) { header('Location: /admin/subcategorias'); exit; }

        $categorias = Categoria::all('ASC');

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subcategoria->sincronizar($_POST);
            $alertas = $subcategoria->validar();
            if(empty($alertas)) {
                $subcategoria->guardar();
                header('Location: /admin/subcategorias');
                exit;
            }
        }

        $router->render('admin/subcategorias/editar', [
            'titulo'       => 'Editar subcategoría',
            'subcategoria' => $subcategoria,
            'categorias'   => $categorias,
            'alertas'      => Subcategoria::getAlertas()
        ]);
    }

    public static function eliminarSubcategoria() {
        if(!is_admin()) { header('Location: /login'); exit; }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $subcategoria = Subcategoria::find($id);
            if($subcategoria) $subcategoria->eliminar();
            header('Location: /admin/subcategorias');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════
    // ÓRDENES
    // ══════════════════════════════════════════════════════

    public static function ordenes(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if(!$pagina_actual || $pagina_actual < 1) { header('Location: /admin/ordenes?page=1'); exit; }

        $por_pagina = 10;
        $total      = Orden::total();
        $offset     = ($pagina_actual - 1) * $por_pagina;
        $ordenes    = Orden::paginar($por_pagina, $offset);

        foreach($ordenes as $orden) {
            $orden->usuario = Usuario::find($orden->usuario_id);
        }

        $router->render('admin/ordenes/index', [
            'titulo'        => 'Órdenes',
            'ordenes'       => $ordenes,
            'pagina_actual' => $pagina_actual,
            'total_paginas' => ceil($total / $por_pagina)
        ]);
    }

    // ══════════════════════════════════════════════════════
    // USUARIOS
    // ══════════════════════════════════════════════════════

    public static function usuarios(Router $router) {
        if(!is_admin()) { header('Location: /login'); exit; }

        $usuarios = Usuario::all('ASC');
        $router->render('admin/usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => $usuarios
        ]);
    }
}
