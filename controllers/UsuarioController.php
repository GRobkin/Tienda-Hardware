<?php

namespace Controllers;

use Model\Usuario;
use Model\Orden;
use MVC\Router;

class UsuarioController
{

    // Mi cuenta
    public static function dashboard(Router $router)
    {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        $usuario = Usuario::find($_SESSION['id']);
        if (!$usuario) {
            header('Location: /login');
            exit;
        }

        $ordenes  = Orden::whereArray(['usuario_id' => $usuario->id]);
        $recientes = array_slice(array_reverse($ordenes), 0, 3);

        $router->render('usuario/dashboard', [
            'titulo'   => 'Mi cuenta',
            'usuario'  => $usuario,
            'total_ordenes' => count($ordenes),
            'ordenes'  => $recientes
        ]);
    }

    // Modificar perfil / contraseña
    public static function modificar(Router $router)
    {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        $usuario = Usuario::find($_SESSION['id']);
        if (!$usuario) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } elseif (($_POST['accion'] ?? '') === 'perfil') {

                $usuario->nombre   = trim($_POST['nombre']   ?? '');
                $usuario->apellido = trim($_POST['apellido'] ?? '');
                $usuario->email    = trim($_POST['email']    ?? '');

                $alertas = $usuario->validarPerfil();

                if (empty($alertas)) {
                    $existe = Usuario::where('email', $usuario->email);
                    if ($existe && $existe->id != $usuario->id) {
                        Usuario::setAlerta('error', 'Ese email ya está en uso por otra cuenta');
                    } else {
                        unset($usuario->password2);
                        $usuario->guardar();

                        // Mantener la sesión al día
                        $_SESSION['nombre']   = $usuario->nombre;
                        $_SESSION['apellido'] = $usuario->apellido;
                        $_SESSION['email']    = $usuario->email;

                        flash('exito', 'Perfil actualizado correctamente');
                        header('Location: /cuenta/modificar');
                        exit;
                    }
                }
            } elseif (($_POST['accion'] ?? '') === 'password') {

                $usuario->password_actual = $_POST['password_actual'] ?? '';
                $nueva                    = $_POST['password_nuevo']  ?? '';

                if (!$usuario->comprobarPassword()) {
                    Usuario::setAlerta('error', 'La contraseña actual es incorrecta');
                } elseif (strlen($nueva) < 6) {
                    Usuario::setAlerta('error', 'La nueva contraseña debe tener al menos 6 caracteres');
                } else {
                    $usuario->password = password_hash($nueva, PASSWORD_BCRYPT);
                    unset($usuario->password2);
                    $usuario->guardar();

                    flash('exito', 'Contraseña actualizada correctamente');
                    header('Location: /cuenta/modificar');
                    exit;
                }
            }
        }

        $router->render('usuario/modificar', [
            'titulo'  => 'Modificar mi cuenta',
            'usuario' => $usuario,
            'alertas' => Usuario::getAlertas()
        ]);
    }
}
