<?php
namespace Controllers;

use Model\Producto;
use MVC\Router;

class CarritoController {

    // Ver carrito
    public static function index(Router $router) {
        // Los administradores no compran
        if(is_admin()) { header('Location: /admin/dashboard'); exit; }

        $carrito   = $_SESSION['carrito'] ?? [];
        $productos = [];
        $total     = 0;

        foreach($carrito as $id => $cantidad) {
            $producto = Producto::find($id);
            if($producto) {
                $producto->cantidad   = $cantidad;
                $producto->subtotal   = $producto->precio * $cantidad;
                $total               += $producto->subtotal;
                $productos[]          = $producto;
            }
        }

        $router->render('carrito/index', [
            'titulo'    => 'Mi carrito',
            'productos' => $productos,
            'total'     => $total
        ]);
    }

    // Agregar al carrito (POST vía fetch)
    public static function agregar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Solicitud inválida']);
            return;
        }

        // Los administradores no compran
        if(is_admin()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Los administradores no pueden comprar']);
            return;
        }

        $id       = filter_var($_POST['id']       ?? 0, FILTER_VALIDATE_INT);
        $cantidad = filter_var($_POST['cantidad'] ?? 1, FILTER_VALIDATE_INT);

        if(!$id || $cantidad < 1) {
            echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos']);
            return;
        }

        $producto = Producto::find($id);
        if(!$producto || $producto->stock < 1) {
            echo json_encode(['ok' => false, 'mensaje' => 'Producto sin stock']);
            return;
        }

        // Sumar si ya existe, agregar si es nuevo
        if(isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }

        // No superar el stock disponible
        if($_SESSION['carrito'][$id] > $producto->stock) {
            $_SESSION['carrito'][$id] = $producto->stock;
        }

        $total_items = array_sum($_SESSION['carrito']);
        echo json_encode(['ok' => true, 'total_items' => $total_items]);
    }

    // Actualizar cantidad (POST vía fetch)
    public static function actualizar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]);
            return;
        }

        $id       = filter_var($_POST['id']       ?? 0, FILTER_VALIDATE_INT);
        $cantidad = filter_var($_POST['cantidad'] ?? 1, FILTER_VALIDATE_INT);

        if(!$id) {
            echo json_encode(['ok' => false]);
            return;
        }

        if($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $producto = Producto::find($id);
            $_SESSION['carrito'][$id] = min($cantidad, $producto->stock ?? $cantidad);
        }

        echo json_encode(['ok' => true]);
    }

    // Eliminar item del carrito
    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]);
            return;
        }

        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        if(isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
        }
        echo json_encode(['ok' => true]);
    }

    // Vaciar carrito
    public static function vaciar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]);
            return;
        }
        $_SESSION['carrito'] = [];
        echo json_encode(['ok' => true]);
    }
}
