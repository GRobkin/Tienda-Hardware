<?php

namespace Controllers;

use Model\Orden;
use Model\OrdenItem;
use Model\Producto;
use MVC\Router;

class OrdenController
{

    // Checkout: formulario de pago ficticio
    public static function checkout(Router $router)
    {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }
        // Los administradores no compran
        if (is_admin()) {
            header('Location: /admin/dashboard');
            exit;
        }

        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            header('Location: /carrito');
            exit;
        }

        // Calcular total y armar productos del carrito
        $productos = [];
        $total     = 0;
        foreach ($carrito as $id => $cantidad) {
            $producto = Producto::find($id);
            if ($producto) {
                $producto->cantidad = $cantidad;
                $producto->subtotal = $producto->precio * $cantidad;
                $total += $producto->subtotal;
                $productos[] = $producto;
            }
        }

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Orden::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                // Validar datos de pago ficticios
                $orden = new Orden([
                    'usuario_id'     => $_SESSION['id'],
                    'nombre_pago'    => $_POST['nombre_pago']    ?? '',
                    'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
                    'total'          => $total,
                    'token'          => bin2hex(random_bytes(6)) // 12 caracteres impredecibles
                ]);

                $alertas = $orden->validarPago();

                // Re-verificar stock contra la base antes de cobrar
                if (empty($alertas)) {
                    foreach ($productos as $producto) {
                        $actual = Producto::find($producto->id);
                        if (!$actual || $actual->stock < $producto->cantidad) {
                            Orden::setAlerta('error', "Stock insuficiente de {$producto->nombre} (quedan " . ($actual->stock ?? 0) . ")");
                        }
                    }
                    $alertas = Orden::getAlertas();
                }

                if (empty($alertas)) {
                    // Nunca guardar el número completo: solo los últimos 4 dígitos
                    $digitos = preg_replace('/\D/', '', $orden->numero_tarjeta);
                    $orden->numero_tarjeta = '**** **** **** ' . substr($digitos, -4);
                    $orden->estado = 'pagado';

                    // Orden + items + stock en una única transacción
                    $db = Orden::getDB();
                    $db->begin_transaction();

                    try {
                        $resultado = $orden->guardar();
                        if (!$resultado['resultado']) {
                            throw new \Exception('No se pudo crear la orden');
                        }
                        $orden_id = $resultado['id'];

                        foreach ($productos as $producto) {
                            $item = new OrdenItem([
                                'orden_id'        => $orden_id,
                                'producto_id'     => $producto->id,
                                'cantidad'        => $producto->cantidad,
                                'precio_unitario' => $producto->precio
                            ]);
                            if (!$item->guardar()['resultado']) {
                                throw new \Exception('No se pudo guardar un item');
                            }

                            // Descontar stock
                            $producto->stock -= $producto->cantidad;
                            if (!$producto->guardar()) {
                                throw new \Exception('No se pudo actualizar el stock');
                            }
                        }

                        $db->commit();
                    } catch (\Throwable $e) {
                        $db->rollback();
                        Orden::setAlerta('error', 'Ocurrió un error al procesar el pago, intentá de nuevo');
                        $alertas = Orden::getAlertas();
                    }

                    if (empty($alertas)) {
                        // Vaciar carrito
                        $_SESSION['carrito'] = [];

                        header('Location: /orden/confirmacion?token=' . urlencode($orden->token));
                        exit;
                    }
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

    // Confirmación de compra
    public static function confirmacion(Router $router)
    {
        $token = s($_GET['token'] ?? '');
        if (!$token) {
            header('Location: /');
            exit;
        }

        $orden = Orden::where('token', $token);
        if (!$orden) {
            header('Location: /');
            exit;
        }

        // Solo el dueño puede ver su orden (o admin)
        if (!is_admin() && $_SESSION['id'] != $orden->usuario_id) {
            header('Location: /');
            exit;
        }

        // Cargar items con datos del producto
        $items_raw = OrdenItem::whereArray(['orden_id' => $orden->id]);
        foreach ($items_raw as $item) {
            $item->producto = Producto::find($item->producto_id);
        }

        $router->render('orden/confirmacion', [
            'titulo' => 'Compra confirmada',
            'orden'  => $orden,
            'items'  => $items_raw
        ]);
    }

    // Mis pedidos (usuario autenticado)
    public static function misPedidos(Router $router)
    {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        $ordenes = Orden::whereArray(['usuario_id' => $_SESSION['id']]);

        $router->render('orden/mis-pedidos', [
            'titulo'  => 'Mis pedidos',
            'ordenes' => $ordenes
        ]);
    }
}
