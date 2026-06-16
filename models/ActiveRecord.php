<?php

namespace Model;

// Clase base de todos los modelos. Cada objeto representa una fila
// de la tabla y trae los métodos para leer, crear, editar y borrar.
class ActiveRecord
{

    protected static $db;              // conexión mysqli
    protected static $tabla = '';      // nombre de la tabla (lo define cada modelo)
    protected static $columnasDB = []; // columnas de la tabla
    protected static $alertas = [];    // mensajes de error/éxito

    // Guarda la conexión a la base (se llama una vez al iniciar)
    public static function setDB($database)
    {
        self::$db = $database;
    }

    // Devuelve la conexión (para usar transacciones en los controladores)
    public static function getDB()
    {
        return self::$db;
    }

    // Agrega un mensaje de alerta
    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Devuelve todas las alertas
    public static function getAlertas()
    {
        return static::$alertas;
    }

    // Validación base (cada modelo la sobreescribe con sus reglas)
    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Ejecuta una consulta y devuelve un array de objetos del modelo
    public static function consultarSQL($query)
    {
        $resultado = self::$db->query($query);

        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }
        $resultado->free();

        return $array;
    }

    // Convierte una fila de la base en un objeto del modelo
    protected static function crearObjeto($registro)
    {
        $objeto = new static;
        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    // Devuelve los valores del objeto que son columnas (sin el id)
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    // Igual que atributos() pero escapa los valores contra inyección SQL
    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }

    // Vuelca los datos de un array (ej: $_POST) sobre el objeto
    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    // Guarda el objeto: INSERT si es nuevo, UPDATE si ya tiene id
    public function guardar()
    {
        if (!is_null($this->id)) {
            return $this->actualizar();
        } else {
            return $this->crear();
        }
    }

    // Devuelve todos los registros ordenados por id
    public static function all($orden = 'DESC')
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id {$orden}";
        return self::consultarSQL($query);
    }

    // Busca un registro por id
    public static function find($id)
    {
        $id = (int) $id;
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Devuelve los últimos N registros
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT {$limite}";
        return self::consultarSQL($query);
    }

    // Devuelve una página de registros
    public static function paginar($por_pagina, $offset)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT {$por_pagina} OFFSET {$offset}";
        return self::consultarSQL($query);
    }

    // Busca el primer registro donde una columna tenga cierto valor
    public static function where($columna, $valor)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Devuelve todos los registros ordenados por una columna
    public static function ordenar($columna, $orden)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY {$columna} {$orden}";
        return self::consultarSQL($query);
    }

    // Igual que ordenar() pero con límite de resultados
    public static function ordenarLimite($columna, $orden, $limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY {$columna} {$orden} LIMIT {$limite}";
        return self::consultarSQL($query);
    }

    // Busca registros que cumplan varias condiciones (AND)
    public static function whereArray($array = [])
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        foreach ($array as $key => $value) {
            $value = self::$db->escape_string($value);
            if ($key == array_key_last($array)) {
                $query .= " {$key} = '{$value}';";
            } else {
                $query .= " {$key} = '{$value}' AND";
            }
        }
        return self::consultarSQL($query);
    }

    // Cuenta registros (opcionalmente filtrando por una columna)
    public static function total($columna = '', $valor = '')
    {
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        if ($columna) {
            $valor = self::$db->escape_string($valor);
            $query .= " WHERE {$columna} = '{$valor}'";
        }
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    // Igual que total() pero con varias condiciones
    public static function totalArray($array = [])
    {
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ";
        foreach ($array as $key => $value) {
            $value = self::$db->escape_string($value);
            if ($key == array_key_last($array)) {
                $query .= " {$key} = '{$value}' ";
            } else {
                $query .= " {$key} = '{$value}' AND ";
            }
        }
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    // Inserta el objeto como nuevo registro
    public function crear()
    {
        $atributos = $this->sanitizarAtributos();

        $query  = "INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos));
        $query .= " ') ";

        $resultado = self::$db->query($query);

        return [
            'resultado' => $resultado,
            'id'        => self::$db->insert_id
        ];
    }

    // Actualiza el registro existente
    public function actualizar()
    {
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key}='{$value}'";
        }

        $query  = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 ";

        return self::$db->query($query);
    }

    // Borra el registro por su id
    public function eliminar()
    {
        $query = "DELETE FROM " . static::$tabla
            . " WHERE id = " . self::$db->escape_string($this->id)
            . " LIMIT 1";
        return self::$db->query($query);
    }
}
