# Tienda Hardware — Guía de archivos del proyecto

Cada carpeta y cada archivo explicados con su código real. Qué son, para qué sirven y qué hace cada cosa importante dentro de ellos.

---

## Cómo fluye una request

Cuando alguien entra a cualquier URL del sitio, siempre pasa lo mismo:

```
Navegador → public/index.php → Router → Controlador → Modelo → Base de datos
                                                    ↓
                                              Vista PHP → layout.php → HTML al navegador
```

El proyecto usa el patrón **MVC** (Modelo–Vista–Controlador) hecho a mano, sin ningún framework externo.

---

## `public/` — Lo que el navegador puede pedir directamente

Esta es la única carpeta expuesta al servidor web. Todo lo demás (modelos, controladores, vistas, config) vive un nivel arriba y no es accesible desde internet.

| Archivo | Qué es |
|---------|--------|
| `index.php` | El punto de entrada único. Toda URL pasa por acá primero. Carga la app y registra las rutas. |
| `css/estilo.css` | Todos los estilos del sitio en un archivo (~3300 líneas). Variables CSS, nav, slider, carrito, admin, dark mode. |
| `js/app.js` | Todo el JavaScript: carrito, buscador, dark mode, slider, validaciones, panel catálogo, admin. Sin jQuery. |
| `js/vendor/sweetalert2.all.min.js` | La única librería externa. Usada para los popups de confirmación y los toasts. |
| `img/banners/` | Imágenes del slider del home. |
| `img/productos/` | Fotos de productos subidas desde el admin. |
| `img/catalogo/` | Íconos SVG por tipo de producto. Aparecen cuando un producto no tiene foto. |

---

## `includes/` — Configuración y arranque

Estos archivos corren al inicio de cada request. No generan HTML ni tienen lógica de negocio, solo preparan el entorno.

---

### `includes/database.php`

Crea la conexión a MySQL. Si falla, para todo y muestra el error. Configura el charset a `utf8mb4` para que funcionen tildes, ñ y caracteres especiales.

```php
<?php
$db = mysqli_connect(
    'localhost',
    'root',
    '',
    'tienda_hardware'
);

if(!$db) {
    echo "Error: No se pudo conectar a MySQL. " . mysqli_connect_error();
    exit;
}

mysqli_set_charset($db, 'utf8mb4');
```

---

### `includes/app.php`

El bootstrap: lo que arranca todo. Hace cuatro cosas en orden:
1. Requiere `Router.php`, `funciones.php` y `database.php`
2. Arranca la sesión de PHP si no estaba activa
3. Registra el **autoloader** — en vez de poner `require 'models/Usuario.php'` a mano en cada archivo, el autoloader lo hace solo la primera vez que se usa cada clase
4. Le pasa la conexión al ORM: `ActiveRecord::setDB($db)`

```php
<?php
require __DIR__ . '/../router/Router.php';
require __DIR__ . '/funciones.php';
require __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader: convierte "Model\Usuario" → models/Usuario.php automáticamente
spl_autoload_register(function($class) {
    $base  = __DIR__ . '/..';
    $class = str_replace('\\', '/', $class);

    $rutas = [
        $base . '/' . strtolower(explode('/', $class)[0]) . 's/' . basename($class) . '.php',
        $base . '/models/'      . basename($class) . '.php',
        $base . '/controllers/' . basename($class) . '.php',
        $base . '/router/'      . basename($class) . '.php',
    ];

    foreach($rutas as $ruta) {
        if(file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

use Model\ActiveRecord;
ActiveRecord::setDB($db);
```

---

### `includes/funciones.php`

Funciones de utilidad disponibles en todo el proyecto.

```php
<?php

// Escapa HTML — se usa en TODAS las vistas para evitar XSS
function s($html) : string {
    return htmlspecialchars($html ?? '');
}

// Verifica si hay alguien logueado
function is_auth() : bool {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

// Verifica si el usuario es admin
function is_admin() : bool {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

// Devuelve "US$ 1.234,56" — formato uruguayo
function formatear_precio($precio) : string {
    return 'US$ ' . number_format((float)$precio, 2, ',', '.');
}

// Devuelve la URL de la imagen del producto.
// Primero busca la foto real; si no existe, devuelve el ícono SVG del catálogo.
function imagen_producto($producto) : string {
    $imagen = $producto->imagen ?? '';
    if ($imagen && $imagen !== 'default.webp') {
        $ruta = __DIR__ . '/../public/img/productos/' . $imagen;
        if (is_file($ruta)) {
            return '/img/productos/' . $imagen;
        }
    }
    $slug = _subcategorias_slug_por_id()[(int)($producto->subcategoria_id ?? 0)] ?? '';
    return '/img/catalogo/' . icono_catalogo_por_slug($slug) . '.svg';
}

// Suma todas las cantidades del carrito (para el badge del nav)
function total_carrito() : int {
    return array_sum($_SESSION['carrito'] ?? []);
}

// --- CSRF ---

// Genera el token secreto una sola vez por sesión
function csrf_token() : string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

// El <input hidden> con el token, listo para pegar en cualquier form
function csrf_field() : string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Verifica que el token del POST coincida con el de sesión
// Usa hash_equals() para evitar timing attacks
function csrf_check() : bool {
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

// --- Flash messages (mensajes que sobreviven a un redirect) ---

function flash($tipo, $mensaje) : void {
    $_SESSION['flash'][$tipo][] = $mensaje;
}

// Lee y BORRA los mensajes para que no aparezcan dos veces
function obtener_flash() : array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}
```

---

## `router/Router.php`

La clase que conecta URLs con controladores. Tiene tres responsabilidades:

- **`get()` / `post()`** — registra una ruta
- **`comprobarRutas()`** — mira la URL actual y ejecuta el controlador que corresponde
- **`render()`** — captura el HTML de la vista en un buffer y lo inyecta dentro de `layout.php`

```php
<?php
namespace MVC;

class Router {
    public array $getRoutes  = [];
    public array $postRoutes = [];

    public function get($url, $fn)  { $this->getRoutes[$url]  = $fn; }
    public function post($url, $fn) { $this->postRoutes[$url] = $fn; }

    // Lee la URL actual y llama al controlador registrado. Si no existe, va al 404.
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

    // Captura la vista en un buffer, guarda el resultado en $contenido
    // y lo inyecta dentro de layout.php
    public function render($view, $datos = []) {
        foreach($datos as $key => $value) {
            $$key = $value;   // convierte el array en variables locales
        }

        ob_start();
        include __DIR__ . "/../views/{$view}.php";
        $contenido = ob_get_clean();  // todo el HTML de la vista queda acá

        include __DIR__ . "/../views/layout.php";  // layout usa $contenido
    }
}
```

---

## `public/index.php` — Todas las rutas

Punto de entrada único: carga la app y registra cada URL con su controlador.

```php
<?php
require_once __DIR__ . '/../includes/app.php';

$router = new Router();

// Páginas públicas
$router->get('/',         [PaginasController::class, 'index']);
$router->get('/producto', [PaginasController::class, 'producto']);
$router->get('/categoria-producto/categoria',    [PaginasController::class, 'categoria']);
$router->get('/categoria-producto/subcategoria', [PaginasController::class, 'subcategoria']);
$router->get('/buscar',   [BuscadorController::class, 'buscar']);

// Auth
$router->get('/login',     [AuthController::class, 'login']);
$router->post('/login',    [AuthController::class, 'login']);
$router->post('/logout',   [AuthController::class, 'logout']);
$router->get('/registro',  [AuthController::class, 'registro']);
$router->post('/registro', [AuthController::class, 'registro']);

// Carrito
$router->post('/carrito/agregar',    [CarritoController::class, 'agregar']);
$router->post('/carrito/actualizar', [CarritoController::class, 'actualizar']);
$router->post('/carrito/eliminar',   [CarritoController::class, 'eliminar']);
$router->post('/carrito/vaciar',     [CarritoController::class, 'vaciar']);

// Orden
$router->get('/checkout',           [OrdenController::class, 'checkout']);
$router->post('/checkout',          [OrdenController::class, 'checkout']);
$router->get('/orden/confirmacion', [OrdenController::class, 'confirmacion']);
$router->get('/mis-pedidos',        [OrdenController::class, 'misPedidos']);

// Admin
$router->get('/admin/dashboard',           [AdminController::class, 'dashboard']);
$router->get('/admin/productos',           [AdminController::class, 'productos']);
$router->get('/admin/productos/crear',     [AdminController::class, 'crearProducto']);
$router->post('/admin/productos/crear',    [AdminController::class, 'crearProducto']);
$router->post('/admin/productos/eliminar', [AdminController::class, 'eliminarProducto']);
// ... y más

$router->comprobarRutas();
```

---

## `models/` — Los modelos

Los modelos son clases PHP donde cada objeto representa una fila de una tabla de la base de datos. Toda la comunicación con MySQL pasa por acá.

---

### `models/ActiveRecord.php`

La clase base de la que heredan todos los modelos. Implementa el CRUD genérico para que cada modelo no lo repita.

```php
<?php
namespace Model;

class ActiveRecord {

    protected static $db;              // conexión mysqli compartida
    protected static $tabla = '';      // la define cada modelo hijo
    protected static $columnasDB = []; // columnas que se leen/escriben en la BD
    protected static $alertas = [];    // mensajes de validación

    // Se llama una sola vez en app.php
    public static function setDB($database) {
        self::$db = $database;
    }

    // Devuelve la conexión (para usar transacciones en los controladores)
    public static function getDB() {
        return self::$db;
    }

    // Ejecuta SQL y devuelve un array de objetos del modelo
    public static function consultarSQL($query) {
        $resultado = self::$db->query($query);
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }
        $resultado->free();
        return $array;
    }

    // Busca por ID
    public static function find($id) {
        $id = (int) $id;
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Busca el primer registro donde una columna tenga cierto valor
    public static function where($columna, $valor) {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Varios registros con varias condiciones AND
    public static function whereArray($array = []) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            $value = self::$db->escape_string($value);
            $query .= ($key == array_key_last($array))
                ? " {$key} = '{$value}';"
                : " {$key} = '{$value}' AND";
        }
        return self::consultarSQL($query);
    }

    // INSERT si no tiene ID, UPDATE si ya tiene
    public function guardar() {
        if(!is_null($this->id)) {
            return $this->actualizar();
        } else {
            return $this->crear();
        }
    }

    // INSERT — los valores pasan por escape_string() antes de entrar al SQL
    public function crear() {
        $atributos = $this->sanitizarAtributos();
        $query  = "INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos));
        $query .= " ') ";
        $resultado = self::$db->query($query);
        return ['resultado' => $resultado, 'id' => self::$db->insert_id];
    }

    // UPDATE
    public function actualizar() {
        $atributos = $this->sanitizarAtributos();
        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key}='{$value}'";
        }
        $query  = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' LIMIT 1";
        return self::$db->query($query);
    }

    // DELETE
    public function eliminar() {
        $query = "DELETE FROM " . static::$tabla
               . " WHERE id = " . self::$db->escape_string($this->id)
               . " LIMIT 1";
        return self::$db->query($query);
    }

    // Carga datos de $_POST sobre el objeto — solo toca los campos que existen en el modelo
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    // Devuelve los valores listos para el SQL (con escape_string en cada uno)
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }
}
```

---

### `models/Categoria.php` y `models/Subcategoria.php`

Categoría representa las secciones principales del catálogo (Componentes, Periféricos, etc.). Subcategoría está un nivel más abajo (CPU, GPU, Mouse, etc.).

```php
// Categoria.php
class Categoria extends ActiveRecord {
    protected static $tabla = 'categorias';
    protected static $columnasDB = ['id','nombre','slug','descripcion'];

    // Busca por slug en vez de por ID (para las URLs amigables)
    public static function porSlug($slug) {
        return self::where('slug', $slug);
    }
}

// Subcategoria.php
class Subcategoria extends ActiveRecord {
    protected static $tabla = 'subcategorias';
    protected static $columnasDB = ['id','nombre','slug','categoria_id','descripcion'];

    // Trae todas las subcategorías de una categoría
    public static function porCategoria($categoria_id) {
        return self::whereArray(['categoria_id' => $categoria_id]);
    }

    // Busca por slug
    public static function porSlug($slug) {
        return self::where('slug', $slug);
    }
}
```

---

### `models/Producto.php`

El modelo más complejo. Tiene métodos propios para los listados filtrados, el buscador y las marcas disponibles.

```php
<?php
namespace Model;

class Producto extends ActiveRecord {
    protected static $tabla = 'productos';
    protected static $columnasDB = ['id','nombre','marca','descripcion','precio',
                                    'stock','imagen','subcategoria_id','destacado'];

    // Valida los campos antes de guardar
    public function validar() {
        if (!$this->nombre)      self::$alertas['error'][] = 'El nombre es obligatorio';
        if (!$this->descripcion) self::$alertas['error'][] = 'La descripción es obligatoria';
        if ($this->precio <= 0)  self::$alertas['error'][] = 'El precio debe ser mayor a 0';
        if ($this->stock < 0)    self::$alertas['error'][] = 'El stock no puede ser negativo';
        return self::$alertas;
    }

    // Lista blanca de criterios de orden — el valor del usuario nunca entra directo al SQL
    public static $ordenes = [
        'recientes'   => 'id DESC',
        'precio_asc'  => 'precio ASC',
        'precio_desc' => 'precio DESC',
        'nombre'      => 'nombre ASC',
    ];

    // Construye el WHERE dinámico según los filtros activos
    private static function whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max) : string {
        $ids   = array_map('intval', $subcategoria_ids);
        $where = ' WHERE subcategoria_id IN (' . (implode(',', $ids) ?: '0') . ')';

        if (!empty($marcas)) {
            $escapadas = array_map(fn($m) => "'" . self::$db->escape_string($m) . "'", $marcas);
            $where .= ' AND marca IN (' . implode(',', $escapadas) . ')';
        }
        if ($precio_min !== null) $where .= ' AND precio >= ' . (float) $precio_min;
        if ($precio_max !== null) $where .= ' AND precio <= ' . (float) $precio_max;

        return $where;
    }

    // Listado paginado con filtros — usado en páginas de categoría y subcategoría
    public static function filtrar($subcategoria_ids, $marcas, $precio_min, $precio_max,
                                   $por_pagina, $offset, $orden = 'recientes') {
        $query = 'SELECT * FROM productos'
               . self::whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max)
               . ' ORDER BY ' . (self::$ordenes[$orden] ?? 'id DESC')
               . ' LIMIT ' . (int)$por_pagina . ' OFFSET ' . (int)$offset;
        return self::consultarSQL($query);
    }

    // Cuenta el total de resultados — para saber cuántas páginas hay
    public static function filtrarTotal($subcategoria_ids, $marcas, $precio_min, $precio_max) : int {
        $query = 'SELECT COUNT(*) FROM productos'
               . self::whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max);
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return (int) array_shift($total);
    }

    // Las marcas disponibles para mostrar en el sidebar de filtros
    public static function marcasDisponibles($subcategoria_ids) : array {
        $ids = array_map('intval', $subcategoria_ids);
        $query = "SELECT DISTINCT marca FROM productos
                  WHERE subcategoria_id IN (" . (implode(',', $ids) ?: '0') . ")
                    AND marca != ''
                  ORDER BY marca ASC";
        $resultado = self::$db->query($query);
        $marcas = [];
        while ($fila = $resultado->fetch_array()) $marcas[] = $fila[0];
        return $marcas;
    }

    // Búsqueda con LIKE en nombre, marca y descripción
    public static function buscar($termino) {
        $termino = self::$db->escape_string($termino);
        $query = "SELECT p.*, s.nombre AS subcategoria_nombre, c.nombre AS categoria_nombre
                  FROM productos p
                  INNER JOIN subcategorias s ON p.subcategoria_id = s.id
                  INNER JOIN categorias c ON s.categoria_id = c.id
                  WHERE p.nombre LIKE '%{$termino}%'
                     OR p.marca LIKE '%{$termino}%'
                     OR p.descripcion LIKE '%{$termino}%'
                  ORDER BY p.nombre ASC
                  LIMIT 8";
        return self::consultarSQL($query);
    }
}
```

---

### `models/Usuario.php`

Maneja la lógica de cuentas de usuario: validaciones, hashing de contraseña y verificación.

```php
<?php
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id','nombre','apellido','email','password','admin'];

    // Validación para el login: email con formato válido y contraseña no vacía
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        if(!$this->password) self::$alertas['error'][] = 'La contraseña no puede estar vacía';
        return self::$alertas;
    }

    // Validación para el registro: todo + que las contraseñas coincidan
    public function validarCuenta() {
        if(!$this->nombre)   self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->apellido) self::$alertas['error'][] = 'El apellido es obligatorio';
        if(!$this->email || !filter_var($this->email, FILTER_VALIDATE_EMAIL))
                             self::$alertas['error'][] = 'Email no válido';
        if(strlen($this->password) < 6)
                             self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
        if($this->password !== $this->password2)
                             self::$alertas['error'][] = 'Las contraseñas no coinciden';
        return self::$alertas;
    }

    // Reemplaza la contraseña en texto plano por su hash bcrypt
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Verifica la contraseña actual contra el hash guardado en la BD
    public function comprobarPassword() : bool {
        return password_verify($this->password_actual, $this->password);
    }
}
```

---

### `models/Orden.php` y `models/OrdenItem.php`

```php
// Orden.php — representa una compra
class Orden extends ActiveRecord {
    protected static $tabla = 'ordenes';
    // creado_en NO está en $columnasDB → la BD lo gestiona sola con DEFAULT NOW
    protected static $columnasDB = ['id','token','usuario_id','estado','total',
                                    'nombre_pago','numero_tarjeta'];

    // Valida los datos del formulario de pago
    public function validarPago() {
        if(!$this->nombre_pago)    self::$alertas['error'][] = 'El titular es obligatorio';
        if(!$this->numero_tarjeta) self::$alertas['error'][] = 'El número de tarjeta es obligatorio';
        elseif(!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $this->numero_tarjeta)))
                                   self::$alertas['error'][] = 'La tarjeta debe tener 16 dígitos';
        return self::$alertas;
    }
}

// OrdenItem.php — una línea de la orden (producto + cantidad + precio al momento de comprar)
class OrdenItem extends ActiveRecord {
    protected static $tabla = 'orden_items';
    protected static $columnasDB = ['id','orden_id','producto_id','cantidad','precio_unitario'];
    // Sin métodos propios, hereda todo de ActiveRecord
}
```

---

## `controllers/` — Los controladores

Los controladores reciben la request, llaman a los modelos y pasan los datos a la vista.

---

### `controllers/AuthController.php`

Login, logout y registro. El más importante es `login()`.

```php
<?php
namespace Controllers;

class AuthController {

    public static function login(Router $router) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            if(!csrf_check()) {
                // Alguien intentó enviar el form sin el token correcto
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario = new Usuario($_POST);
                $alertas = $usuario->validarLogin();  // valida email y password

                if(empty($alertas)) {
                    // Busca el usuario por email en la BD
                    $usuario = Usuario::where('email', $usuario->email);

                    if(!$usuario) {
                        Usuario::setAlerta('error', 'El usuario no existe');
                    } elseif(!password_verify($_POST['password'], $usuario->password)) {
                        // Compara lo que escribió con el hash de la BD
                        Usuario::setAlerta('error', 'Contraseña incorrecta');
                    } else {
                        // Cambia el ID de sesión para prevenir session fixation
                        session_regenerate_id(true);

                        $_SESSION['id']       = $usuario->id;
                        $_SESSION['nombre']   = $usuario->nombre;
                        $_SESSION['apellido'] = $usuario->apellido;
                        $_SESSION['email']    = $usuario->email;
                        $_SESSION['admin']    = $usuario->admin ?? null;

                        // Admin → panel, cliente → home
                        header('Location: ' . ($usuario->admin ? '/admin/dashboard' : '/'));
                        exit;
                    }
                }
            }
        }

        $router->render('auth/login', [
            'titulo'  => 'Iniciar sesión',
            'alertas' => Usuario::getAlertas()
        ]);
    }

    public static function logout() {
        // Solo acepta POST con CSRF — evita que un link pueda cerrar la sesión
        if($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
            $_SESSION = [];
            session_destroy();
        }
        header('Location: /login');
        exit;
    }

    public static function registro(Router $router) {
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!csrf_check()) {
                Usuario::setAlerta('error', 'La sesión expiró, intentá de nuevo');
            } else {
                $usuario->sincronizar($_POST);         // carga los campos del form
                $alertas = $usuario->validarCuenta();  // valida todo

                if(empty($alertas)) {
                    $existe = Usuario::where('email', $usuario->email);
                    if($existe) {
                        Usuario::setAlerta('error', 'Ese email ya está registrado');
                    } else {
                        $usuario->hashPassword();   // convierte a hash bcrypt
                        unset($usuario->password2); // no se guarda en la BD

                        $resultado = $usuario->guardar();  // INSERT
                        if($resultado['resultado']) {
                            header('Location: /mensaje');
                            exit;
                        }
                    }
                }
            }
        }

        $router->render('auth/registro', [
            'titulo'  => 'Crear cuenta',
            'usuario' => $usuario,
            'alertas' => Usuario::getAlertas()
        ]);
    }
}
```

---

### `controllers/CarritoController.php`

El carrito vive en `$_SESSION['carrito']` como un array `[id_producto => cantidad]`. Todos los métodos devuelven JSON y bloquean admins.

```php
<?php
namespace Controllers;

class CarritoController {

    // Ver el carrito — carga los productos de la BD y calcula subtotales
    public static function index(Router $router) {
        if(is_admin()) { header('Location: /admin/dashboard'); exit; }

        $carrito   = $_SESSION['carrito'] ?? [];
        $productos = [];
        $total     = 0;

        foreach($carrito as $id => $cantidad) {
            $producto = Producto::find($id);
            if($producto) {
                $producto->cantidad = $cantidad;
                $producto->subtotal = $producto->precio * $cantidad;
                $total             += $producto->subtotal;
                $productos[]        = $producto;
            }
        }

        $router->render('carrito/index', [
            'titulo'    => 'Mi carrito',
            'productos' => $productos,
            'total'     => $total
        ]);
    }

    // Agregar — POST vía fetch desde JavaScript
    public static function agregar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Solicitud inválida']);
            return;
        }
        if(is_admin()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Los administradores no pueden comprar']);
            return;
        }

        $id       = filter_var($_POST['id']      ?? 0, FILTER_VALIDATE_INT);
        $cantidad = filter_var($_POST['cantidad'] ?? 1, FILTER_VALIDATE_INT);

        $producto = Producto::find($id);
        if(!$producto || $producto->stock < 1) {
            echo json_encode(['ok' => false, 'mensaje' => 'Producto sin stock']);
            return;
        }

        // Suma si ya existe, agrega si es nuevo
        if(isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }

        // Nunca superar el stock disponible
        if($_SESSION['carrito'][$id] > $producto->stock) {
            $_SESSION['carrito'][$id] = $producto->stock;
        }

        $total_items = array_sum($_SESSION['carrito']);
        echo json_encode(['ok' => true, 'total_items' => $total_items]);
    }

    // Actualizar cantidad
    public static function actualizar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]); return;
        }
        $id       = filter_var($_POST['id']      ?? 0, FILTER_VALIDATE_INT);
        $cantidad = filter_var($_POST['cantidad'] ?? 1, FILTER_VALIDATE_INT);

        if($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);  // cantidad 0 → elimina el item
        } else {
            $producto = Producto::find($id);
            $_SESSION['carrito'][$id] = min($cantidad, $producto->stock ?? $cantidad);
        }
        echo json_encode(['ok' => true]);
    }

    // Eliminar un item
    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]); return;
        }
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        unset($_SESSION['carrito'][$id]);
        echo json_encode(['ok' => true]);
    }

    // Vaciar todo
    public static function vaciar() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
            echo json_encode(['ok' => false]); return;
        }
        $_SESSION['carrito'] = [];
        echo json_encode(['ok' => true]);
    }
}
```

---

### `controllers/OrdenController.php` — checkout()

El método más importante del proyecto. Confirma una compra con una transacción MySQL.

```php
public static function checkout(Router $router) {
    if(!is_auth()) { header('Location: /login'); exit; }
    if(is_admin()) { header('Location: /admin/dashboard'); exit; }

    $carrito = $_SESSION['carrito'] ?? [];
    if(empty($carrito)) { header('Location: /carrito'); exit; }

    // Arma el array de productos con cantidades y subtotales
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

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(!csrf_check()) {
            Orden::setAlerta('error', 'La sesión expiró, intentá de nuevo');
        } else {
            $orden = new Orden([
                'usuario_id'     => $_SESSION['id'],
                'nombre_pago'    => $_POST['nombre_pago']    ?? '',
                'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
                'total'          => $total,
                'token'          => bin2hex(random_bytes(6))  // 12 chars aleatorios
            ]);

            $alertas = $orden->validarPago();

            // Re-verifica stock en tiempo real — puede haber cambiado desde que abrió el form
            if(empty($alertas)) {
                foreach($productos as $producto) {
                    $actual = Producto::find($producto->id);
                    if(!$actual || $actual->stock < $producto->cantidad) {
                        Orden::setAlerta('error', "Stock insuficiente de {$producto->nombre}");
                    }
                }
                $alertas = Orden::getAlertas();
            }

            if(empty($alertas)) {
                // Solo guarda los últimos 4 dígitos de la tarjeta
                $digitos = preg_replace('/\D/', '', $orden->numero_tarjeta);
                $orden->numero_tarjeta = '**** **** **** ' . substr($digitos, -4);
                $orden->estado = 'pagado';

                // Transacción MySQL: si algo falla en el medio, rollback
                $db = Orden::getDB();
                $db->begin_transaction();

                try {
                    $resultado = $orden->guardar();           // INSERT ordenes
                    if(!$resultado['resultado']) {
                        throw new \Exception('No se pudo crear la orden');
                    }
                    $orden_id = $resultado['id'];

                    foreach($productos as $producto) {
                        $item = new OrdenItem([            // INSERT orden_items
                            'orden_id'        => $orden_id,
                            'producto_id'     => $producto->id,
                            'cantidad'        => $producto->cantidad,
                            'precio_unitario' => $producto->precio
                        ]);
                        if(!$item->guardar()['resultado']) {
                            throw new \Exception('No se pudo guardar un item');
                        }

                        $producto->stock -= $producto->cantidad; // UPDATE stock
                        if(!$producto->guardar()) {
                            throw new \Exception('No se pudo actualizar el stock');
                        }
                    }

                    $db->commit();  // todo salió bien

                } catch(\Throwable $e) {
                    $db->rollback();  // algo falló → deshace todo
                    Orden::setAlerta('error', 'Ocurrió un error al procesar el pago');
                }

                if(empty(Orden::getAlertas())) {
                    $_SESSION['carrito'] = [];  // vacía el carrito
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
```

---

## `views/` — Las vistas

Archivos PHP que generan el HTML. Reciben variables del controlador y las muestran. Por sí solas no hacen nada — el router las llama.

### `views/layout.php` — La plantilla base

Envuelve todo el contenido. Detecta si la ruta es de auth o de admin para adaptar el nav y el footer. Pone el token CSRF en un `<meta>` para que el JS lo use en los fetch.

```php
<?php
$ruta    = $_SERVER['PATH_INFO'] ?? '/';
$es_auth = in_array($ruta, ['/login', '/registro', '/mensaje']);
$es_admin = str_starts_with($ruta, '/admin');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta name="csrf" content="<?= csrf_token() ?>">  <!-- El JS lo lee para AJAX -->
    <link rel="stylesheet" href="/css/estilo.css?v=<?= filemtime(...) ?>"> <!-- cache busting -->
</head>
<body>
    <?php if ($es_auth): ?>
        <?php include 'parciales/nav-auth.php'; ?>  <!-- Solo el logo -->
    <?php else: ?>
        <?php include 'parciales/nav.php'; ?>       <!-- Nav completo -->
    <?php endif; ?>

    <?php if ($es_admin): ?>
        <?php include 'parciales/admin-tabs.php'; ?>  <!-- Tabs del admin -->
    <?php endif; ?>

    <main>
        <?= $contenido ?>   <!-- El HTML de la vista específica va acá -->
    </main>

    <?php if (!$es_admin): ?>
        <?php include 'parciales/footer.php'; ?>
    <?php endif; ?>

    <script src="/js/vendor/sweetalert2.all.min.js"></script>
    <script src="/js/app.js?v=<?= filemtime(...) ?>"></script>
</body>
</html>
```

---

### `views/parciales/` — Fragmentos reutilizables

| Archivo | Qué es |
|---------|--------|
| `nav.php` | Nav principal. Inyecta las categorías/subcategorías como JSON (`window.SUBCATEGORIAS`) para el panel catálogo. |
| `nav-auth.php` | Solo el logo. Para login y registro. |
| `admin-tabs.php` | Tabs del admin (Dashboard / Productos / Órdenes / Usuarios). Resalta la activa. |
| `alertas.php` | Combina los mensajes flash de sesión con los del controlador y los muestra. |
| `filtros.php` | Sidebar de filtros: select de orden, checkboxes de marcas, inputs de precio. |
| `footer.php` | Dos versiones: footer completo para la tienda, simplificado para auth. |
| `paginacion.php` | Botones Anterior / números / Siguiente. |
| `producto-card.php` | Tarjeta reutilizable: imagen, nombre, precio, botón de carrito. Si es admin no muestra el botón. |

El parcial `alertas.php` es un buen ejemplo de cómo funcionan los flash messages:

```php
<?php
// Combina los mensajes del redirect (flash) con los del controlador actual
$alertas = array_merge_recursive(obtener_flash(), $alertas ?? []);
?>
<div aria-live="polite">
<?php if (!empty($alertas)): ?>
    <?php foreach ($alertas as $tipo => $mensajes): ?>
        <?php foreach ((array) $mensajes as $mensaje): ?>
        <div class="alerta alerta--<?= s($tipo) ?>" role="alert">
            <?= s($mensaje) ?>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>
</div>
```

---

### `views/auth/` — Páginas de autenticación

| Archivo | Qué muestra |
|---------|-------------|
| `login.php` | Formulario de login (email + contraseña) con toggle de contraseña y validación JS en cada campo. |
| `registro.php` | Formulario de registro con validación en tiempo real campo por campo. |
| `mensaje.php` | Pantalla de "cuenta creada". Solo texto y link al login. |

---

### `views/paginas/` — Páginas públicas generales

| Archivo | Qué muestra |
|---------|-------------|
| `index.php` | Home: slider de banners, productos destacados, tiles de categorías, recién llegados. |
| `sobre.php` | Página estática "Sobre nosotros". |
| `garantia.php` | Información sobre garantías y cómo hacer un reclamo. |
| `contacto.php` | Formulario de contacto (no envía email real, es demo). |
| `error.php` | La página 404 con links para volver o reportar el problema. |

---

### `views/tienda/` — Páginas de la tienda

| Archivo | Qué muestra |
|---------|-------------|
| `categoria.php` | Listado de una categoría completa: breadcrumb, chips de subcategorías, sidebar de filtros, grilla con paginación. |
| `subcategoria.php` | Igual pero filtrado a una subcategoría. El chip activo aparece resaltado. |
| `producto.php` | Detalle: imagen, precio, badge de stock (en stock / últimas N unidades / sin stock), stepper de cantidad, descripción, productos relacionados. |

---

### `views/carrito/` y `views/orden/`

| Archivo | Qué muestra |
|---------|-------------|
| `carrito/index.php` | La página del carrito. Si está vacío muestra estado vacío. Si tiene productos: tabla con steppers, subtotales, botón eliminar y resumen con total. |
| `orden/checkout.php` | El formulario de pago (demo). A la izquierda el form, a la derecha el resumen del pedido. |
| `orden/confirmacion.php` | Lo que ve el usuario después de comprar: token de orden, estado, tabla de ítems, total pagado. |
| `orden/mis-pedidos.php` | Historial de compras: tabla con token, fecha, estado, total y link al detalle. |

---

### `views/usuario/` y `views/admin/`

| Archivo | Qué muestra |
|---------|-------------|
| `usuario/dashboard.php` | "Mi cuenta": datos del usuario y últimas 3 órdenes. |
| `usuario/modificar.php` | Dos forms en una página diferenciados por campo oculto `name="accion"` (perfil o password). |
| `admin/dashboard.php` | 3 tarjetas de métricas + últimas 5 órdenes. |
| `admin/productos/index.php` | Tabla paginada de productos con editar/eliminar. |
| `admin/productos/crear.php` | Form de producto: select de categoría que filtra subcategorías en JS, input de imagen con preview instantáneo. |
| `admin/productos/editar.php` | Igual que crear pero con los campos pre-cargados. |
| `admin/ordenes/index.php` | Tabla paginada de órdenes. |
| `admin/ordenes/crear.php` | Orden manual: filas dinámicas de productos y total en tiempo real. |
| `admin/usuarios/index.php` | Lista de todos los usuarios registrados. |

---

## `public/js/app.js` — El JavaScript

Todo en un archivo, todo dentro de `DOMContentLoaded`.

### `postForm()` — la función central para AJAX

Lee el token CSRF del `<meta>` del layout y lo adjunta a cada petición.

```js
function postForm(url, datos) {
    const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
    return fetch(url, {
        method: 'POST',
        body: new URLSearchParams({ ...datos, csrf })
    }).then(r => r.json());
}
```

### Agregar al carrito — event delegation

Un solo listener captura clicks en cualquier `.agregar-carrito` de la página.

```js
document.addEventListener('click', e => {
    const btn = e.target.closest('.agregar-carrito');
    if (!btn) return;

    const id = btn.dataset.id;
    let cantidad = 1;
    if (btn.dataset.cantidad) {
        const input = document.querySelector(btn.dataset.cantidad);
        cantidad = Math.max(1, parseInt(input?.value, 10) || 1);
    }

    btn.disabled = true;
    const textoOriginal = btn.textContent;

    postForm('/carrito/agregar', { id, cantidad })
        .then(data => {
            if (data.ok) {
                window.actualizarContadorCarrito(data.total_items);
                avisar('success', 'Producto agregado al carrito');
                btn.textContent = '¡Agregado!';
            } else {
                avisar('error', data.mensaje || 'No se pudo agregar');
                btn.textContent = textoOriginal;
            }
            setTimeout(() => {
                btn.textContent = textoOriginal;
                btn.disabled = false;
            }, 1500);
        });
});
```

### Buscador — debounce

Espera que el usuario deje de escribir antes de hacer el fetch.

```js
inputBuscador.addEventListener('input', () => {
    clearTimeout(timeoutBuscador);
    const q = inputBuscador.value.trim();
    if (q.length < 2) { resultadosBuscador.hidden = true; return; }
    timeoutBuscador = setTimeout(() => buscar(q), 300);
});

async function buscar(q) {
    const res = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
    const data = await res.json();
    renderBuscador(data);
}

// Los resultados se construyen con createElement — nunca innerHTML con datos externos
function renderBuscador(productos) {
    productos.forEach(prod => {
        const a = document.createElement('a');
        const nombre = document.createElement('span');
        nombre.textContent = prod.nombre;  // textContent es seguro, innerHTML no
        a.appendChild(nombre);
        resultadosBuscador.appendChild(a);
    });
    resultadosBuscador.hidden = false;
}
```

### Dark mode

```js
aplicarTema(localStorage.getItem('tema') || 'light');

themeBtn?.addEventListener('click', () => {
    const nuevo = html.dataset.theme === 'dark' ? 'light' : 'dark';
    aplicarTema(nuevo);
    localStorage.setItem('tema', nuevo);
});

function aplicarTema(tema) {
    html.dataset.theme = tema;  // El CSS reacciona solo con [data-theme="dark"]
    iconMoon.style.display = tema === 'dark' ? 'none' : 'inline-block';
    iconSun.style.display  = tema === 'dark' ? 'inline-block' : 'none';
}
```

### Checkout — formato de tarjeta y validación

```js
// Autoformato mientras se escribe: "1234567890123456" → "1234 5678 9012 3456"
iTarjeta?.addEventListener('input', () => {
    const digitos = iTarjeta.value.replace(/\D/g, '').slice(0, 16);
    iTarjeta.value = digitos.replace(/(\d{4})(?=\d)/g, '$1 ');
});

// Valida antes de enviar el formulario
formCheckout.addEventListener('submit', e => {
    const digitos = (iTarjeta?.value || '').replace(/\D/g, '');
    if (!digitos || digitos.length !== 16) {
        e.preventDefault();  // no envía el form
        marcarError(iTarjeta, eTarjeta, 'Debe tener 16 dígitos');
    }
});
```

### Formularios con confirmación (eliminar productos)

```js
// Los forms con clase js-confirm piden confirmación antes de enviarse
document.querySelectorAll('form.js-confirm').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        confirmarAccion(form.dataset.mensaje || '¿Confirmar?').then(ok => {
            if (ok) form.submit();  // submit() nativo no re-dispara el listener
        });
    });
});
```

### Admin — selects dependientes (categoría → subcategoría)

```js
const selCategoria    = document.getElementById('selectCategoria');
const selSubcategoria = document.getElementById('selectSubcategoria');

if (selCategoria && selSubcategoria && window.ADMIN_SUBCATS) {
    const filtrarSubcategorias = () => {
        const catId  = parseInt(selCategoria.value, 10);
        const actual = parseInt(selSubcategoria.dataset.actual, 10) || null;

        // Filtra el JSON que inyectó PHP — sin petición al servidor
        const subs = window.ADMIN_SUBCATS.filter(s => s.categoria_id === catId);

        selSubcategoria.innerHTML = '';
        subs.forEach(s => {
            selSubcategoria.add(new Option(s.nombre, s.id, false, s.id === actual));
        });
    };

    selCategoria.addEventListener('change', filtrarSubcategorias);
    filtrarSubcategorias();  // carga las subcategorías al abrir el form
}
```

---

## Seguridad

| Amenaza | Cómo se previene en este proyecto |
|---------|----------------------------------|
| **XSS** | Todo dato de la BD pasa por `s()` antes de mostrarse en HTML. En JS se usa `textContent` en vez de `innerHTML` con datos externos. |
| **CSRF** | Todos los POST tienen un token secreto verificado con `hash_equals()`. Los AJAX lo leen del `<meta>`. |
| **SQL Injection** | `escape_string()` en todos los valores del ORM. Los criterios de orden usan una whitelist. |
| **Contraseñas** | `password_hash()` con bcrypt. Nunca en texto plano. `password_verify()` para comparar. |
| **Session fixation** | `session_regenerate_id(true)` al hacer login — cambia el ID de sesión e invalida el anterior. |
| **Datos de tarjeta** | Solo se guardan los últimos 4 dígitos. El número completo nunca se persiste. |
| **Archivos subidos** | Se valida el tipo MIME real con `finfo_open()`, no solo la extensión del archivo. |
