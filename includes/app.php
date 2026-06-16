<?php
require __DIR__ . '/../router/Router.php';
require __DIR__ . '/funciones.php';
require __DIR__ . '/database.php';

// Sesión única para toda la aplicación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader simple para Models y Controllers
spl_autoload_register(function ($class) {
    // Convierte "Model\Usuario" => models/Usuario.php
    //           "Controllers\AuthController" => controllers/AuthController.php
    $base  = __DIR__ . '/..';
    $class = str_replace('\\', '/', $class);

    $rutas = [
        $base . '/' . strtolower(explode('/', $class)[0]) . 's/' . basename($class) . '.php',
        $base . '/models/'      . basename($class) . '.php',
        $base . '/controllers/' . basename($class) . '.php',
        $base . '/router/'      . basename($class) . '.php',
    ];

    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

use Model\ActiveRecord;

ActiveRecord::setDB($db);
