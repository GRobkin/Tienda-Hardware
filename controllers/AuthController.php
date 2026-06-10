<?php
namespace Controllers;

use Model\Usuario;
use MVC\Router;

class AuthController {

    // ── Login ──────────────────────────────────────────────
    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                } elseif(!password_verify($_POST['password'], $usuario->password)) {
                    Usuario::setAlerta('error', 'Contraseña incorrecta');
                } else {
                    session_start();
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

        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'titulo'  => 'Iniciar sesión',
            'alertas' => $alertas
        ]);
    }

    // ── Logout ─────────────────────────────────────────────
    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $_SESSION = [];
            session_destroy();
            header('Location: /login');
            exit;
        }
    }

    // ── Registro ───────────────────────────────────────────
    public static function registro(Router $router) {
        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarCuenta();

            if(empty($alertas)) {
                $existe = Usuario::where('email', $usuario->email);

                if($existe) {
                    Usuario::setAlerta('error', 'Ese email ya está registrado');
                } else {
                    $usuario->hashPassword();
                    unset($usuario->password2);
                    $usuario->crearToken();
                    $usuario->confirmado = 1;
                    
                    $resultado = $usuario->guardar();
                    if($resultado['resultado']) {
                        header('Location: /mensaje');
                        exit;
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

    // ── Confirmar cuenta ───────────────────────────────────
    public static function confirmar(Router $router) {
        $token = s($_GET['token'] ?? '');
        if(!$token) { header('Location: /'); exit; }

        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            $usuario->confirmado = 1;
            $usuario->token      = '';
            unset($usuario->password2);
            $usuario->guardar();
            Usuario::setAlerta('exito', '¡Cuenta confirmada! Ya podés iniciar sesión');
        }

        $router->render('auth/confirmar', [
            'titulo'  => 'Confirmar cuenta',
            'alertas' => Usuario::getAlertas()
        ]);
    }

    // ── Olvidé mi contraseña ───────────────────────────────
    public static function olvide(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado) {
                    $usuario->crearToken();
                    unset($usuario->password2);
                    $usuario->guardar();
                    // Aquí iría el envío de email; por ahora mostramos el token en alerta
                    Usuario::setAlerta('exito', 'Revisá tu email para restablecer la contraseña');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }

        $router->render('auth/olvide', [
            'titulo'  => 'Olvidé mi contraseña',
            'alertas' => Usuario::getAlertas()
        ]);
    }

    // ── Restablecer contraseña ─────────────────────────────
    public static function restablecer(Router $router) {
        $token       = s($_GET['token'] ?? '');
        $token_valido = true;

        if(!$token) { header('Location: /'); exit; }

        $usuario = Usuario::where('token', $token);
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $token_valido = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                $usuario->hashPassword();
                $usuario->token = null;
                $usuario->guardar();
                header('Location: /login');
                exit;
            }
        }

        $router->render('auth/restablecer', [
            'titulo'      => 'Restablecer contraseña',
            'alertas'     => Usuario::getAlertas(),
            'token_valido' => $token_valido
        ]);
    }

    // ── Mensaje post-registro ──────────────────────────────
    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada'
        ]);
    }
}
