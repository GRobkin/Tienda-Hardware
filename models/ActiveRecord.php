<?php

namespace Model;

/**
 * Clase base ActiveRecord
 * 
 * Esta clase es la base de todos los modelos del proyecto.
 * Implementa el patrón "Active Record": cada objeto PHP representa
 * una fila en la base de datos, y la clase tiene métodos para
 * leer, crear, actualizar y eliminar registros (CRUD).
 * 
 * Los modelos como Usuario, Producto, Pedido, etc. extienden esta clase
 * y heredan todos estos métodos automáticamente.
 */
class ActiveRecord {

    // ─────────────────────────────────────────────
    // PROPIEDADES ESTÁTICAS (compartidas por todos los objetos de la clase)
    // ─────────────────────────────────────────────

    /** @var mysqli Conexión activa a la base de datos MySQL */
    protected static $db;

    /** @var string Nombre de la tabla en la base de datos (se sobreescribe en cada modelo) */
    protected static $tabla = '';

    /** @var array Lista de columnas de la tabla (se sobreescribe en cada modelo) */
    protected static $columnasDB = [];

    /** @var array Mensajes de error o éxito generados durante validaciones */
    protected static $alertas = [];


    // ─────────────────────────────────────────────
    // CONFIGURACIÓN
    // ─────────────────────────────────────────────

    /**
     * Guarda la conexión a la base de datos en la propiedad estática $db.
     * Se llama una sola vez al iniciar la aplicación (en includes/app.php).
     * 
     * @param mysqli $database Conexión creada con mysqli_connect()
     */
    public static function setDB($database) {
        self::$db = $database;
    }


    // ─────────────────────────────────────────────
    // ALERTAS Y VALIDACIÓN
    // ─────────────────────────────────────────────

    /**
     * Agrega un mensaje de alerta (error, éxito, aviso, etc.).
     * 
     * Ejemplo de uso:
     *   Usuario::setAlerta('error', 'El email ya está registrado');
     *   Usuario::setAlerta('exito', 'Cuenta creada correctamente');
     * 
     * @param string $tipo    Tipo de alerta: 'error', 'exito', 'aviso', etc.
     * @param string $mensaje Texto del mensaje
     */
    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    /**
     * Devuelve todas las alertas acumuladas hasta el momento.
     * Se usa en los controladores para pasar los mensajes a las vistas.
     * 
     * @return array Array asociativo con tipo => [mensajes]
     */
    public static function getAlertas() {
        return static::$alertas;
    }

    /**
     * Método de validación base (vacío).
     * Cada modelo lo sobreescribe con sus propias reglas.
     * 
     * Ejemplo en Producto.php:
     *   public function validar() {
     *       if (!$this->nombre) self::$alertas['error'][] = 'El nombre es obligatorio';
     *       return self::$alertas;
     *   }
     * 
     * @return array Alertas (vacío si no hay errores)
     */
    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }


    // ─────────────────────────────────────────────
    // CONSULTAS A LA BASE DE DATOS
    // ─────────────────────────────────────────────

    /**
     * Ejecuta una consulta SQL y devuelve un array de objetos del modelo.
     * 
     * Este método es interno: lo usan all(), find(), where(), etc.
     * No se llama directamente desde los controladores.
     * 
     * @param  string $query Consulta SQL completa
     * @return array  Array de objetos del modelo correspondiente
     */
    public static function consultarSQL($query) {
        // Ejecutar la consulta en la base de datos
        $resultado = self::$db->query($query);

        // Recorrer cada fila devuelta y convertirla en un objeto PHP
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // Liberar la memoria usada por el resultado de MySQL
        $resultado->free();

        return $array;
    }

    /**
     * Convierte una fila de la base de datos (array asociativo)
     * en un objeto del modelo correspondiente.
     * 
     * Por ejemplo, una fila de la tabla 'productos' se convierte
     * en un objeto Producto con sus propiedades ($id, $nombre, $precio...).
     * 
     * @param  array $registro Fila de la BD como array ['columna' => 'valor']
     * @return static          Objeto del modelo con las propiedades asignadas
     */
    protected static function crearObjeto($registro) {
        // Crear una instancia vacía del modelo que llama al método
        $objeto = new static;

        // Asignar cada valor de la fila a la propiedad correspondiente del objeto
        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }


    // ─────────────────────────────────────────────
    // MANEJO DE ATRIBUTOS
    // ─────────────────────────────────────────────

    /**
     * Devuelve los atributos del objeto que corresponden a columnas de la BD,
     * excluyendo el 'id' (que no se envía en INSERT).
     * 
     * Se usa en crear() y actualizar() para armar las consultas SQL.
     * 
     * @return array Array asociativo ['columna' => valor]
     */
    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue; // El ID lo maneja MySQL automáticamente
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    /**
     * Igual que atributos(), pero pasa cada valor por escape_string()
     * para prevenir inyección SQL antes de guardar en la BD.
     * 
     * Ejemplo: si alguien escribe  ' OR '1'='1  en un campo,
     * escape_string lo neutraliza para que no afecte la consulta.
     * 
     * @return array Array asociativo con valores saneados
     */
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }

    /**
     * Actualiza las propiedades del objeto con los valores de un array.
     * Se usa para aplicar los datos de $_POST al objeto antes de guardarlo.
     * 
     * Solo actualiza propiedades que existan en el objeto y que no sean null.
     * 
     * Ejemplo de uso en un controlador:
     *   $producto->sincronizar($_POST);
     * 
     * @param array $args Array de datos (generalmente $_POST)
     */
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }


    // ─────────────────────────────────────────────
    // CRUD — CREAR, LEER, ACTUALIZAR, ELIMINAR
    // ─────────────────────────────────────────────

    /**
     * Guarda el objeto en la base de datos.
     * 
     * Decide automáticamente si hacer INSERT o UPDATE:
     * - Si el objeto NO tiene id → es nuevo → llama a crear()
     * - Si el objeto YA tiene id → existe en la BD → llama a actualizar()
     * 
     * @return array|bool Resultado de crear() o actualizar()
     */
    public function guardar() {
        if(!is_null($this->id)) {
            return $this->actualizar(); // Ya existe, actualizamos
        } else {
            return $this->crear();      // Es nuevo, insertamos
        }
    }

    /**
     * Devuelve todos los registros de la tabla.
     * 
     * Ejemplo de uso:
     *   $productos = Producto::all();           // orden DESC por defecto
     *   $categorias = Categoria::all('ASC');    // orden ASC
     * 
     * @param  string $orden 'ASC' o 'DESC'
     * @return array  Array de objetos del modelo
     */
    public static function all($orden = 'DESC') {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id {$orden}";
        return self::consultarSQL($query);
    }

    /**
     * Busca un registro por su ID.
     * 
     * Ejemplo de uso:
     *   $producto = Producto::find(3);  // devuelve el producto con id=3
     * 
     * @param  int         $id ID del registro
     * @return static|null     Objeto del modelo, o null si no existe
     */
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado); // Devuelve el primer (y único) resultado
    }

    /**
     * Devuelve los últimos N registros de la tabla.
     * 
     * Ejemplo de uso:
     *   $ultimos = Pedido::get(5);  // los 5 pedidos más recientes
     * 
     * @param  int   $limite Cantidad de registros a traer
     * @return array Array de objetos del modelo
     */
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT {$limite}";
        return self::consultarSQL($query);
    }

    /**
     * Devuelve registros con paginación (para mostrar por páginas).
     * 
     * Ejemplo de uso:
     *   $productos = Producto::paginar(10, 0);   // página 1 (offset 0)
     *   $productos = Producto::paginar(10, 10);  // página 2 (offset 10)
     * 
     * @param  int   $por_pagina Cantidad de registros por página
     * @param  int   $offset     Desde qué registro empezar
     * @return array Array de objetos del modelo
     */
    public static function paginar($por_pagina, $offset) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT {$por_pagina} OFFSET {$offset}";
        return self::consultarSQL($query);
    }

    /**
     * Busca el primer registro donde una columna tenga determinado valor.
     * 
     * Ejemplo de uso:
     *   $usuario = Usuario::where('email', 'juan@mail.com');
     * 
     * @param  string      $columna Nombre de la columna
     * @param  mixed       $valor   Valor a buscar
     * @return static|null          Primer resultado, o null si no existe
     */
    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    /**
     * Devuelve todos los registros ordenados por una columna.
     * 
     * Ejemplo de uso:
     *   $productos = Producto::ordenar('nombre', 'ASC');
     * 
     * @param  string $columna Columna por la que ordenar
     * @param  string $orden   'ASC' o 'DESC'
     * @return array  Array de objetos del modelo
     */
    public static function ordenar($columna, $orden) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY {$columna} {$orden}";
        return self::consultarSQL($query);
    }

    /**
     * Igual que ordenar(), pero limita la cantidad de resultados.
     * 
     * Ejemplo de uso:
     *   $top5 = Producto::ordenarLimite('precio', 'DESC', 5); // los 5 más caros
     * 
     * @param  string $columna Columna por la que ordenar
     * @param  string $orden   'ASC' o 'DESC'
     * @param  int    $limite  Máximo de resultados
     * @return array  Array de objetos del modelo
     */
    public static function ordenarLimite($columna, $orden, $limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY {$columna} {$orden} LIMIT {$limite}";
        return self::consultarSQL($query);
    }

    /**
     * Busca registros que cumplan múltiples condiciones a la vez (AND).
     * 
     * Ejemplo de uso:
     *   $items = PedidoItem::whereArray(['pedido_id' => 5, 'producto_id' => 2]);
     * 
     * @param  array $array Array asociativo ['columna' => valor, ...]
     * @return array Array de objetos que coinciden con todas las condiciones
     */
    public static function whereArray($array = []) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            if($key == array_key_last($array)) {
                $query .= " {$key} = '{$value}';";
            } else {
                $query .= " {$key} = '{$value}' AND";
            }
        }
        return self::consultarSQL($query);
    }

    /**
     * Cuenta el total de registros en la tabla.
     * Si se pasan $columna y $valor, cuenta solo los que cumplan esa condición.
     * 
     * Ejemplo de uso:
     *   $total = Producto::total();                    // todos los productos
     *   $total = Producto::total('categoria_id', 2);  // solo de categoría 2
     * 
     * @param  string $columna (Opcional) Columna para filtrar
     * @param  mixed  $valor   (Opcional) Valor de la columna
     * @return int    Cantidad de registros
     */
    public static function total($columna = '', $valor = '') {
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        if($columna) {
            $query .= " WHERE {$columna} = {$valor}";
        }
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    /**
     * Igual que total(), pero acepta múltiples condiciones a la vez.
     * 
     * Ejemplo de uso:
     *   $cantidad = PedidoItem::totalArray(['pedido_id' => 3, 'producto_id' => 1]);
     * 
     * @param  array $array Array asociativo ['columna' => valor, ...]
     * @return int   Cantidad de registros que cumplen todas las condiciones
     */
    public static function totalArray($array = []) {
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            if($key == array_key_last($array)) {
                $query .= " {$key} = '{$value}' ";
            } else {
                $query .= " {$key} = '{$value}' AND ";
            }
        }
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    /**
     * Inserta un nuevo registro en la base de datos.
     * Se llama desde guardar() cuando el objeto no tiene id.
     * 
     * Arma automáticamente la consulta INSERT con las columnas
     * y valores del objeto, sanitizados para evitar inyección SQL.
     * 
     * @return array ['resultado' => bool, 'id' => int] ID del nuevo registro creado
     */
    public function crear() {
        // Obtener atributos del objeto ya saneados
        $atributos = $this->sanitizarAtributos();

        // Armar la consulta INSERT:
        // INSERT INTO tabla (col1, col2) VALUES ('val1', 'val2')
        $query  = "INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos));
        $query .= " ') ";

        $resultado = self::$db->query($query);

        return [
            'resultado' => $resultado,
            'id'        => self::$db->insert_id // ID generado por MySQL para el nuevo registro
        ];
    }

    /**
     * Actualiza el registro existente en la base de datos.
     * Se llama desde guardar() cuando el objeto ya tiene id.
     * 
     * Arma automáticamente la consulta UPDATE con los campos
     * y valores actuales del objeto.
     * 
     * @return bool true si se actualizó correctamente
     */
    public function actualizar() {
        // Obtener atributos del objeto ya saneados
        $atributos = $this->sanitizarAtributos();

        // Construir el formato  columna='valor'  para cada campo
        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "{$key}='{$value}'";
        }

        // Armar la consulta UPDATE:
        // UPDATE tabla SET col1='val1', col2='val2' WHERE id='X' LIMIT 1
        $query  = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 "; // LIMIT 1 como seguridad: solo afecta un registro

        return self::$db->query($query);
    }

    /**
     * Elimina el registro de la base de datos usando su id.
     * 
     * Ejemplo de uso:
     *   $producto = Producto::find(5);
     *   $producto->eliminar();
     * 
     * @return bool true si se eliminó correctamente
     */
    public function eliminar() {
        $query = "DELETE FROM " . static::$tabla
               . " WHERE id = " . self::$db->escape_string($this->id)
               . " LIMIT 1"; // LIMIT 1 como seguridad

        return self::$db->query($query);
    }
}