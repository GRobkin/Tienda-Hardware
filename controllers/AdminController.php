<?php

namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use Model\Orden;
use Model\OrdenItem;
use Model\Usuario;
use MVC\Router;

class AdminController
{

    // Corta la ejecución si el usuario no es admin
    private static function proteger(): void
    {
        if (!is_admin()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Sube la imagen de producto validando extensión y MIME real.
     * Devuelve el nombre del archivo guardado, o null si no se subió nada
     * o si el archivo fue rechazado (en ese caso deja una alerta).
     */
    private static function subirImagen(): ?string
    {
        if (empty($_FILES['imagen']['tmp_name'])) return null;

        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $mimes      = ['image/jpeg', 'image/png', 'image/webp'];

        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime      = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($extension, $permitidas) || !in_array($mime, $mimes)) {
            Producto::setAlerta('error', 'La imagen debe ser JPG, PNG o WEBP');
            return null;
        }

        $carpeta = __DIR__ . '/../public/img/productos';
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre = md5(uniqid(rand(), true)) . '.' . $extension;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta . '/' . $nombre)) {
            return $nombre;
        }
        return null;
    }

    // Dashboard
    public static function dashboard(Router $router)
    {
        self::proteger();

        $total_productos   = Producto::total();
        $total_ordenes     = Orden::total();
        $total_usuarios    = Usuario::total();
        $ordenes_recientes = Orden::get(5);

        foreach ($ordenes_recientes as $orden) {
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

    // PRODUCTOS

    public static function productos(Router $router)
    {
        self::proteger();

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/productos?page=1');
            exit;
        }

        $por_pagina = 10;
        $total      = Producto::total();
        $offset     = ($pagina_actual - 1) * $por_pagina;
        $productos  = Producto::paginar($por_pagina, $offset);

        foreach ($productos as $producto) {
            $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
            if ($producto->subcategoria) {
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

    public static function crearProducto(Router $router)
    {
        self::proteger();

        $categorias    = Categoria::all('ASC');
        $subcategorias = Subcategoria::all('ASC');
        $producto      = new Producto;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Producto::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $producto->sincronizar($_POST);
                $producto->destacado = isset($_POST['destacado']) ? 1 : 0;

                $imagen = self::subirImagen();
                if ($imagen) $producto->imagen = $imagen;

                $alertas = $producto->validar();
                if (empty($alertas)) {
                    $resultado = $producto->guardar();
                    if ($resultado['resultado']) {
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

    public static function editarProducto(Router $router)
    {
        self::proteger();

        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/productos');
            exit;
        }

        $producto = Producto::find($id);
        if (!$producto) {
            header('Location: /admin/productos');
            exit;
        }

        $categorias    = Categoria::all('ASC');
        $subcategorias = Subcategoria::all('ASC');
        $imagen_actual = $producto->imagen;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Producto::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $producto->sincronizar($_POST);
                $producto->destacado = isset($_POST['destacado']) ? 1 : 0;

                $imagen = self::subirImagen();
                $producto->imagen = $imagen ?: $imagen_actual;

                $alertas = $producto->validar();
                if (empty($alertas)) {
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

    public static function eliminarProducto()
    {
        self::proteger();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $producto = Producto::find($id);
            if ($producto) {
                $producto->eliminar();
                flash('exito', 'Producto eliminado');
            }
        }
        header('Location: /admin/productos');
        exit;
    }

    // ÓRDENES

    public static function ordenes(Router $router)
    {
        self::proteger();

        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/ordenes?page=1');
            exit;
        }

        $por_pagina = 10;
        $total      = Orden::total();
        $offset     = ($pagina_actual - 1) * $por_pagina;
        $ordenes    = Orden::paginar($por_pagina, $offset);

        foreach ($ordenes as $orden) {
            $orden->usuario = Usuario::find($orden->usuario_id);
        }

        $router->render('admin/ordenes/index', [
            'titulo'        => 'Órdenes',
            'ordenes'       => $ordenes,
            'pagina_actual' => $pagina_actual,
            'total_paginas' => (int) ceil($total / $por_pagina)
        ]);
    }

    // Crear una orden manualmente (venta en mostrador, teléfono, etc.)
    public static function crearOrden(Router $router)
    {
        self::proteger();

        $usuarios  = Usuario::all('ASC');
        $productos = Producto::ordenar('nombre', 'ASC');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Orden::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario_id = filter_var($_POST['usuario_id'] ?? 0, FILTER_VALIDATE_INT);
                $estado     = in_array($_POST['estado'] ?? '', ['pendiente', 'pagado', 'cancelado'])
                    ? $_POST['estado'] : 'pendiente';

                // Combinar filas repetidas del mismo producto
                $ids        = (array) ($_POST['producto_id'] ?? []);
                $cantidades = (array) ($_POST['cantidad'] ?? []);
                $items = [];
                foreach ($ids as $i => $pid) {
                    $pid  = (int) $pid;
                    $cant = (int) ($cantidades[$i] ?? 0);
                    if ($pid && $cant > 0) $items[$pid] = ($items[$pid] ?? 0) + $cant;
                }

                if (!$usuario_id || !Usuario::find($usuario_id)) {
                    Orden::setAlerta('error', 'Elegí un cliente válido');
                } elseif (empty($items)) {
                    Orden::setAlerta('error', 'Agregá al menos un producto con cantidad');
                } else {
                    // Verificar stock y armar el detalle
                    $detalle = [];
                    $total   = 0;
                    foreach ($items as $pid => $cant) {
                        $producto = Producto::find($pid);
                        if (!$producto) {
                            Orden::setAlerta('error', 'Uno de los productos no existe');
                        } elseif ($producto->stock < $cant) {
                            Orden::setAlerta('error', "Stock insuficiente de {$producto->nombre} (quedan {$producto->stock})");
                        } else {
                            $detalle[] = [$producto, $cant];
                            $total    += $producto->precio * $cant;
                        }
                    }

                    if (empty(Orden::getAlertas())) {
                        $orden = new Orden([
                            'usuario_id'     => $usuario_id,
                            'estado'         => $estado,
                            'total'          => $total,
                            'nombre_pago'    => 'Carga manual (admin)',
                            'numero_tarjeta' => '—',
                            'token'          => bin2hex(random_bytes(6))
                        ]);

                        $db = Orden::getDB();
                        $db->begin_transaction();
                        try {
                            $resultado = $orden->guardar();
                            if (!$resultado['resultado']) throw new \Exception('No se pudo crear la orden');

                            foreach ($detalle as [$producto, $cant]) {
                                $item = new OrdenItem([
                                    'orden_id'        => $resultado['id'],
                                    'producto_id'     => $producto->id,
                                    'cantidad'        => $cant,
                                    'precio_unitario' => $producto->precio
                                ]);
                                if (!$item->guardar()['resultado']) throw new \Exception('No se pudo guardar un item');

                                $producto->stock -= $cant;
                                if (!$producto->guardar()) throw new \Exception('No se pudo actualizar el stock');
                            }

                            $db->commit();
                            flash('exito', "Orden {$orden->token} creada correctamente");
                            header('Location: /admin/ordenes');
                            exit;
                        } catch (\Throwable $e) {
                            $db->rollback();
                            Orden::setAlerta('error', 'Ocurrió un error al crear la orden, intentá de nuevo');
                        }
                    }
                }
            }
        }

        $router->render('admin/ordenes/crear', [
            'titulo'    => 'Nueva orden',
            'usuarios'  => $usuarios,
            'productos' => $productos,
            'alertas'   => Orden::getAlertas()
        ]);
    }

    // USUARIOS

    public static function usuarios(Router $router)
    {
        self::proteger();

        $usuarios = Usuario::all('ASC');
        $router->render('admin/usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => $usuarios
        ]);
    }
}
