<?php
namespace Controllers;

use Model\Orden;
use Model\OrdenItem;
use Model\Producto;
use MVC\Router;

class OrdenController {

    // ── Checkout: formulario de pago ficticio ──────────────
    public static function checkout(Router $router) {
        if(!is_auth()) { header('Location: /login'); exit; }

        session_start();
        $carrito = $_SESSION['carrito'] ?? [];

        if(empty($carrito)) { header('Location: /carrito'); exit; }

        // Calcular total y armar productos del carrito
        $productos = [];
        $total     = 0;
        foreach($carrito as $id => $cantidad) {
            $producto = Producto::find($id);
            if($producto) {
                $producto->cantidad = $cantidad;
                $producto->subtotal = $producto->precio * $cantidad;
                $total += $producto->subtotal;
                $productos[] = $producto;
            }
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar datos de pago ficticios
            $orden = new Orden([
                'usuario_id'     => $_SESSION['id'],
                'nombre_pago'    => $_POST['nombre_pago']    ?? '',
                'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
                'total'          => $total,
                'token'          => substr(md5(uniqid(rand(), true)), 0, 12)
            ]);

            $alertas = $orden->validarPago();

            if(empty($alertas)) {
                // Guardar orden
                $resultado = $orden->guardar();

                if($resultado['resultado']) {
                    $orden_id = $resultado['id'];

                    // Guardar items y descontar stock
                    foreach($productos as $producto) {
                        $item = new OrdenItem([
                            'orden_id'        => $orden_id,
                            'producto_id'     => $producto->id,
                            'cantidad'        => $producto->cantidad,
                            'precio_unitario' => $producto->precio
                        ]);
                        $item->guardar();

                        // Descontar stock
                        $producto->stock -= $producto->cantidad;
                        $producto->guardar();
                    }

                    // Marcar como pagado
                    $orden->id     = $orden_id;
                    $orden->estado = 'pagado';
                    $orden->guardar();

                    // Vaciar carrito
                    $_SESSION['carrito'] = [];

                    header('Location: /orden/confirmacion?token=' . urlencode($orden->token));
                    exit;
                }
            }
        }

        $router->render('orden/checkout', [
            'titulo'    => 'Finalizar compra',
            'productos' => $productos,
            'total'     => $total,
            'alertas'   => Orden::getAlertas()
        ]);
    }

    // ── Confirmación de compra ─────────────────────────────
    public static function confirmacion(Router $router) {
        $token = s($_GET['token'] ?? '');
        if(!$token) { header('Location: /'); exit; }

        $orden = Orden::where('token', $token);
        if(!$orden) { header('Location: /'); exit; }

        // Solo el dueño puede ver su orden (o admin)
        session_start();
        if(!is_admin() && $_SESSION['id'] != $orden->usuario_id) {
            header('Location: /'); exit;
        }

        // Cargar items con datos del producto
        $items_raw = OrdenItem::whereArray(['orden_id' => $orden->id]);
        foreach($items_raw as $item) {
            $item->producto = Producto::find($item->producto_id);
        }

        $router->render('orden/confirmacion', [
            'titulo' => 'Compra confirmada',
            'orden'  => $orden,
            'items'  => $items_raw
        ]);
    }

    // ── Mis pedidos (usuario autenticado) ──────────────────
    public static function misPedidos(Router $router) {
        if(!is_auth()) { header('Location: /login'); exit; }

        session_start();
        $ordenes = Orden::whereArray(['usuario_id' => $_SESSION['id']]);

        $router->render('orden/mis-pedidos', [
            'titulo'  => 'Mis pedidos',
            'ordenes' => $ordenes
        ]);
    }
}
