<?php
namespace Controllers;

use Model\Usuario;
use MVC\Router;

class AuthController {

    // ── Login ──────────────────────────────────────────────
    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario = new Usuario($_POST);
                $alertas = $usuario->validarLogin();

                if(empty($alertas)) {
                    $usuario = Usuario::where('email', $usuario->email);

                    if(!$usuario || !$usuario->confirmado) {
                        Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                    } elseif(!password_verify($_POST['password'], $usuario->password)) {
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

    // ── Logout ─────────────────────────────────────────────
    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $_SESSION = [];
            session_destroy();
        }
        header('Location: /login');
        exit;
    }

    // ── Registro ───────────────────────────────────────────
    public static function registro(Router $router) {
        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
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
                        $usuario->confirmado = 0; // requiere confirmar por email

                        $resultado = $usuario->guardar();
                        if($resultado['resultado']) {
                            $enlace = url_sitio('/confirmar?token=' . $usuario->token);

                            enviar_email(
                                $usuario->email,
                                'Confirmá tu cuenta en Tienda Hardware',
                                plantilla_email(
                                    "¡Hola {$usuario->nombre}!",
                                    'Gracias por crear tu cuenta. Para empezar a comprar, confirmala haciendo clic en el botón.',
                                    'Confirmar mi cuenta',
                                    $enlace
                                )
                            );

                            // Modo demo: guardamos el enlace para mostrarlo en pantalla
                            $_SESSION['demo_email_link'] = $enlace;

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
        $alertas   = [];
        $demo_link = '';

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario = new Usuario($_POST);
                $alertas = $usuario->validarEmail();

                if(empty($alertas)) {
                    $usuario = Usuario::where('email', $usuario->email);

                    if($usuario && $usuario->confirmado) {
                        $usuario->crearToken();
                        unset($usuario->password2);
                        $usuario->guardar();

                        $enlace = url_sitio('/restablecer?token=' . $usuario->token);

                        enviar_email(
                            $usuario->email,
                            'Restablecé tu contraseña — Tienda Hardware',
                            plantilla_email(
                                "Hola {$usuario->nombre}",
                                'Recibimos un pedido para restablecer tu contraseña. Si fuiste vos, hacé clic en el botón. Si no, ignorá este mensaje.',
                                'Restablecer contraseña',
                                $enlace
                            )
                        );

                        $demo_link = $enlace; // Modo demo: se muestra en pantalla
                        Usuario::setAlerta('exito', 'Te enviamos un email con el enlace para restablecer la contraseña');
                    } else {
                        Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                    }
                }
            }
        }

        $router->render('auth/olvide', [
            'titulo'    => 'Olvidé mi contraseña',
            'alertas'   => Usuario::getAlertas(),
            'demo_link' => $demo_link
        ]);
    }

    // ── Restablecer contraseña ─────────────────────────────
    public static function restablecer(Router $router) {
        $token        = s($_GET['token'] ?? '');
        $token_valido = true;

        if(!$token) { header('Location: /'); exit; }

        $usuario = Usuario::where('token', $token);
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $token_valido = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
            if(!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario->sincronizar($_POST);
                $alertas = $usuario->validarPassword();

                if(empty($alertas)) {
                    $usuario->hashPassword();
                    $usuario->token = '';
                    unset($usuario->password2);
                    $usuario->guardar();
                    flash('exito', 'Contraseña actualizada. Ya podés iniciar sesión');
                    header('Location: /login');
                    exit;
                }
            }
        }

        $router->render('auth/restablecer', [
            'titulo'       => 'Restablecer contraseña',
            'alertas'      => Usuario::getAlertas(),
            'token_valido' => $token_valido
        ]);
    }

    // ── Mensaje post-registro ──────────────────────────────
    public static function mensaje(Router $router) {
        $demo_link = $_SESSION['demo_email_link'] ?? '';
        unset($_SESSION['demo_email_link']);

        $router->render('auth/mensaje', [
            'titulo'    => 'Cuenta creada',
            'demo_link' => $demo_link
        ]);
    }
}
