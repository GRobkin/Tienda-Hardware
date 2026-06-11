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

    // Corta la ejecución si el usuario no es admin
    private static function proteger() : void {
        if(!is_admin()) { header('Location: /login'); exit; }
    }

    /**
     * Sube la imagen de producto validando extensión y MIME real.
     * Devuelve el nombre del archivo guardado, o null si no se subió nada
     * o si el archivo fue rechazado (en ese caso deja una alerta).
     */
    private static function subirImagen() : ?string {
        if(empty($_FILES['imagen']['tmp_name'])) return null;

        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $mimes      = ['image/jpeg', 'image/png', 'image/webp'];

        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime      = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
        finfo_close($finfo);

        if(!in_array($extension, $permitidas) || !in_array($mime, $mimes)) {
            Producto::setAlerta('error', 'La imagen debe ser JPG, PNG o WEBP');
            return null;
        }

        $carpeta = __DIR__ . '/../public/img/productos';
        if(!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre = md5(uniqid(rand(), true)) . '.' . $extension;
        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . '/' . $nombre)) {
            return $nombre;
        }
        return null;
    }

    // ── Dashboard ──────────────────────────────────────────
    public static function dashboard(Router $router) {
        self::proteger();

        $total_productos   = Producto::total();
        $total_ordenes     = Orden::total();
        $total_usuarios    = Usuario::total();
        $ordenes_recientes = Orden::get(5);

        foreach($ordenes_recientes as $orden) {
            $orden->usuario = Usuario::find($orden->usuario_id);
        }

        $router->render('admin/dashboard', [
            'titulo'            => 'Panel de administración',
            'total_productos'   => $total_productos,
            'total_ordenes'     => $total_ordenes,
            'total_usuarios'    => $total_usuarios,
            'ordenes_recientes' => $ordenes_recientes
        ]);
    }

    // ══════════════════════════════════════════════════════
    // PRODUCTOS
    // ══════════════════════════════════════════════════════

    public static function productos(Router $router) {
        self::proteger();

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if(!$pagina_actual || $pagina_actual < 1) { header('Location: /admin/productos?page=1'); exit; }

        $por_pagina = 10;
        $total      = Producto::total();
        $offset     = ($pagina_actual - 1) * $por_pagina;
        $productos  = Producto::paginar($por_pagina, $offset);

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
            'total_paginas' => (int) ceil($total / $por_pagina)
        ]);
    }

    public static function crearProducto(Router $router) {
        self::proteger();

        $categorias    = Categoria::all('ASC');
        $subcategorias = Subcategoria::all('ASC');
        $producto      = new Producto;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Producto::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $producto->sincronizar($_POST);
                $producto->destacado = isset($_POST['destacado']) ? 1 : 0;

                $imagen = self::subirImagen();
                if($imagen) $producto->imagen = $imagen;

                $alertas = $producto->validar();
                if(empty($alertas)) {
                    $resultado = $producto->guardar();
                    if($resultado['resultado']) {
                        flash('exito', 'Producto creado correctamente');
                        header('Location: /admin/productos');
                        exit;
                    }
                }
            }
        }

        $router->render('admin/productos/crear', [
            'titulo'        => 'Nuevo producto',
            'producto'      => $producto,
            'categorias'    => $categorias,
            'subcategorias' => $subcategorias,
            'alertas'       => Producto::getAlertas()
        ]);
    }

    public static function editarProducto(Router $router) {
        self::proteger();

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if(!$id) { header('Location: /admin/productos'); exit; }

        $producto = Producto::find($id);
        if(!$producto) { header('Location: /admin/productos'); exit; }

        $categorias    = Categoria::all('ASC');
        $subcategorias = Subcategoria::all('ASC');
        $imagen_actual = $producto->imagen;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Producto::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $producto->sincronizar($_POST);
                $producto->destacado = isset($_POST['destacado']) ? 1 : 0;

                $imagen = self::subirImagen();
                $producto->imagen = $imagen ?: $imagen_actual;

                $alertas = $producto->validar();
                if(empty($alertas)) {
                    $producto->guardar();
                    flash('exito', 'Producto actualizado correctamente');
                    header('Location: /admin/productos');
                    exit;
                }
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
        self::proteger();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $producto = Producto::find($id);
            if($producto) {
                $producto->eliminar();
                flash('exito', 'Producto eliminado');
            }
        }
        header('Location: /admin/productos');
        exit;
    }

    // ══════════════════════════════════════════════════════
    // CATEGORÍAS
    // ══════════════════════════════════════════════════════

    public static function categorias(Router $router) {
        self::proteger();

        $categorias = Categoria::all('ASC');
        foreach($categorias as $categoria) {
            $categoria->total_subcategorias = Subcategoria::total('categoria_id', $categoria->id);
        }

        $router->render('admin/categorias/index', [
            'titulo'     => 'Categorías',
            'categorias' => $categorias,
            'alertas'    => Categoria::getAlertas()
        ]);
    }

    public static function crearCategoria() {
        self::proteger();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $categoria = new Categoria([
                'nombre'      => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ]);
            $categoria->slug = generar_slug($categoria->nombre);

            $alertas = $categoria->validar();
            if(empty($alertas)) {
                if(Categoria::where('slug', $categoria->slug)) {
                    flash('error', 'Ya existe una categoría con ese nombre');
                } else {
                    $categoria->guardar();
                    flash('exito', 'Categoría creada');
                }
            } else {
                flash('error', 'El nombre de la categoría es obligatorio');
            }
        }
        header('Location: /admin/categorias');
        exit;
    }

    public static function editarCategoria(Router $router) {
        self::proteger();

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $categoria = $id ? Categoria::find($id) : null;
        if(!$categoria) { header('Location: /admin/categorias'); exit; }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Categoria::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $categoria->nombre      = trim($_POST['nombre'] ?? '');
                $categoria->descripcion = trim($_POST['descripcion'] ?? '');
                $categoria->slug        = generar_slug($categoria->nombre);

                $alertas = $categoria->validar();
                if(empty($alertas)) {
                    $existe = Categoria::where('slug', $categoria->slug);
                    if($existe && $existe->id != $categoria->id) {
                        Categoria::setAlerta('error', 'Ya existe una categoría con ese nombre');
                    } else {
                        $categoria->guardar();
                        flash('exito', 'Categoría actualizada');
                        header('Location: /admin/categorias');
                        exit;
                    }
                }
            }
        }

        $router->render('admin/categorias/editar', [
            'titulo'    => 'Editar categoría',
            'categoria' => $categoria,
            'alertas'   => Categoria::getAlertas()
        ]);
    }

    public static function eliminarCategoria() {
        self::proteger();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $categoria = $id ? Categoria::find($id) : null;

            if($categoria) {
                if(Subcategoria::total('categoria_id', $categoria->id) > 0) {
                    flash('error', 'No se puede eliminar: la categoría tiene subcategorías. Eliminalas o reasignalas primero.');
                } else {
                    $categoria->eliminar();
                    flash('exito', 'Categoría eliminada');
                }
            }
        }
        header('Location: /admin/categorias');
        exit;
    }

    // ══════════════════════════════════════════════════════
    // SUBCATEGORÍAS
    // ══════════════════════════════════════════════════════

    public static function subcategorias(Router $router) {
        self::proteger();

        $subcategorias = Subcategoria::all('ASC');
        $categorias    = Categoria::all('ASC');

        $cats_por_id = [];
        foreach($categorias as $cat) $cats_por_id[$cat->id] = $cat;
        foreach($subcategorias as $sub) {
            $sub->categoria       = $cats_por_id[$sub->categoria_id] ?? null;
            $sub->total_productos = Producto::total('subcategoria_id', $sub->id);
        }

        $router->render('admin/subcategorias/index', [
            'titulo'        => 'Subcategorías',
            'subcategorias' => $subcategorias,
            'categorias'    => $categorias,
            'alertas'       => Subcategoria::getAlertas()
        ]);
    }

    public static function crearSubcategoria() {
        self::proteger();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $subcategoria = new Subcategoria([
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'categoria_id' => filter_var($_POST['categoria_id'] ?? 0, FILTER_VALIDATE_INT) ?: '',
                'descripcion'  => trim($_POST['descripcion'] ?? '')
            ]);
            $subcategoria->slug = generar_slug($subcategoria->nombre);

            $alertas = $subcategoria->validar();
            if(empty($alertas)) {
                if(Subcategoria::where('slug', $subcategoria->slug)) {
                    flash('error', 'Ya existe una subcategoría con ese nombre');
                } else {
                    $subcategoria->guardar();
                    flash('exito', 'Subcategoría creada');
                }
            } else {
                flash('error', 'Completá el nombre y elegí una categoría');
            }
        }
        header('Location: /admin/subcategorias');
        exit;
    }

    public static function editarSubcategoria(Router $router) {
        self::proteger();

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        $subcategoria = $id ? Subcategoria::find($id) : null;
        if(!$subcategoria) { header('Location: /admin/subcategorias'); exit; }

        $categorias = Categoria::all('ASC');

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Subcategoria::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $subcategoria->nombre       = trim($_POST['nombre'] ?? '');
                $subcategoria->categoria_id = filter_var($_POST['categoria_id'] ?? 0, FILTER_VALIDATE_INT) ?: '';
                $subcategoria->descripcion  = trim($_POST['descripcion'] ?? '');
                $subcategoria->slug         = generar_slug($subcategoria->nombre);

                $alertas = $subcategoria->validar();
                if(empty($alertas)) {
                    $existe = Subcategoria::where('slug', $subcategoria->slug);
                    if($existe && $existe->id != $subcategoria->id) {
                        Subcategoria::setAlerta('error', 'Ya existe una subcategoría con ese nombre');
                    } else {
                        $subcategoria->guardar();
                        flash('exito', 'Subcategoría actualizada');
                        header('Location: /admin/subcategorias');
                        exit;
                    }
                }
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
        self::proteger();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $subcategoria = $id ? Subcategoria::find($id) : null;

            if($subcategoria) {
                if(Producto::total('subcategoria_id', $subcategoria->id) > 0) {
                    flash('error', 'No se puede eliminar: hay productos en esta subcategoría. Reasignalos primero.');
                } else {
                    $subcategoria->eliminar();
                    flash('exito', 'Subcategoría eliminada');
                }
            }
        }
        header('Location: /admin/subcategorias');
        exit;
    }

    // ══════════════════════════════════════════════════════
    // ÓRDENES
    // ══════════════════════════════════════════════════════

    public static function ordenes(Router $router) {
        self::proteger();

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
            'total_paginas' => (int) ceil($total / $por_pagina)
        ]);
    }

    // ══════════════════════════════════════════════════════
    // USUARIOS
    // ══════════════════════════════════════════════════════

    public static function usuarios(Router $router) {
        self::proteger();

        $usuarios = Usuario::all('ASC');
        $router->render('admin/usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => $usuarios
        ]);
    }
}
