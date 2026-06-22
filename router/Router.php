<?php
// Recibe las rutas registradas y ejecuta el controlador que corresponde a la URL actual
namespace MVC;

class Router {
    public array $getRoutes  = [];
    public array $postRoutes = [];

    // Registra una ruta GET o POST asociada a una función/método
    public function get($url, $fn)  { $this->getRoutes[$url]  = $fn; }
    public function post($url, $fn) { $this->postRoutes[$url] = $fn; }

    // Lee la URL y el método HTTP actuales y llama al controlador registrado, o redirige a /404
    public function comprobarRutas() {
        $url_actual = $_SERVER['PATH_INFO'] ?? '/';
        $method     = $_SERVER['REQUEST_METHOD'];

        $fn = ($method === 'GET')
            ? ($this->getRoutes[$url_actual]  ?? null)
            : ($this->postRoutes[$url_actual] ?? null);

        if($fn) {
            call_user_func($fn, $this);
        } else {
            header('Location: /404');
        }
    }

    // Renderiza una vista dentro del layout: extrae las variables, captura el HTML con ob y lo inyecta en layout.php
    public function render($view, $datos = []) {
        foreach($datos as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include __DIR__ . "/../views/{$view}.php";
        $contenido = ob_get_clean();// Limpia el Buffer

        include __DIR__ . "/../views/layout.php";
    }
}