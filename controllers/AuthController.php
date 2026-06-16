<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class AuthController
{

    // Login
    public static function login(Router $router)
    {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario = new Usuario($_POST);
                $alertas = $usuario->validarLogin();

                if (empty($alertas)) {
                    $usuario = Usuario::where('email', $usuario->email);

                    if (!$usuario) {
                        Usuario::setAlerta('error', 'El usuario no existe');
                    } elseif (!password_verify($_POST['password'], $usuario->password)) {
                        Usuario::setAlerta('error', 'Contraseña incorrecta');
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['id']       = $usuario->id;
                        $_SESSION['nombre']   = $usuario->nombre;
                        $_SESSION['apellido'] = $usuario->apellido;
                        $_SESSION['email']    = $usuario->email;
                        $_SESSION['admin']    = $usuario->admin ?? null;

                        header('Location: ' . ($usuario->admin ? '/admin/dashboard' : '/'));
                        exit;
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'titulo'  => 'Iniciar sesión',
            'alertas' => $alertas
        ]);
    }

    // Logout
    public static function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $_SESSION = [];
            session_destroy();
        }
        header('Location: /login');
        exit;
    }

    // Registro
    public static function registro(Router $router)
    {
        $usuario = new Usuario;
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario->sincronizar($_POST);
                $alertas = $usuario->validarCuenta();

                if (empty($alertas)) {
                    $existe = Usuario::where('email', $usuario->email);

                    if ($existe) {
                        Usuario::setAlerta('error', 'Ese email ya está registrado');
                    } else {
                        $usuario->hashPassword();
                        unset($usuario->password2);

                        $resultado = $usuario->guardar();
                        if ($resultado['resultado']) {
                            header('Location: /mensaje');
                            exit;
                        }
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/registro', [
            'titulo'  => 'Crear cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    // Mensaje post-registro
    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada'
        ]);
    }
}
