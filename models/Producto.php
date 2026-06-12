<?php

namespace Model;

class Producto extends ActiveRecord
{
    protected static $tabla = 'productos';
    protected static $columnasDB = ['id', 'nombre', 'marca', 'descripcion', 'precio', 'stock', 'imagen', 'subcategoria_id', 'destacado'];

    public $id;
    public $nombre;
    public $marca;
    public $descripcion;
    public $precio;
    public $stock;
    public $imagen;
    public $subcategoria_id;
    public $destacado;
    public $subcategoria;
    public $categoria;
    public $cantidad;
    public $subtotal;
    // Columnas extra de JOINs (buscar): no se guardan en la BD
    public $subcategoria_nombre;
    public $categoria_nombre;
    // Columna de solo lectura
    public $creado_en;

    public function __construct($args = [])
    {
        $this->id              = $args['id']              ?? null;
        $this->nombre          = $args['nombre']          ?? '';
        $this->marca           = $args['marca']           ?? '';
        $this->descripcion     = $args['descripcion']     ?? '';
        $this->precio          = $args['precio']          ?? 0;
        $this->stock           = $args['stock']           ?? 0;
        $this->imagen          = $args['imagen']          ?? 'default.webp';
        $this->subcategoria_id = $args['subcategoria_id'] ?? '';
        $this->destacado       = $args['destacado']       ?? 0;
    }

    public function validar()
    {
        if (!$this->nombre)       self::$alertas['error'][] = 'El nombre del producto es obligatorio';
        if (!$this->descripcion)  self::$alertas['error'][] = 'La descripción es obligatoria';
        if (!$this->precio || !is_numeric($this->precio) || $this->precio <= 0)
            self::$alertas['error'][] = 'El precio debe ser mayor a 0';
        if (!isset($this->stock) || filter_var($this->stock, FILTER_VALIDATE_INT) === false || $this->stock < 0)
            self::$alertas['error'][] = 'El stock debe ser un número entero mayor o igual a 0';
        if (!$this->subcategoria_id || !filter_var($this->subcategoria_id, FILTER_VALIDATE_INT))
            self::$alertas['error'][] = 'Seleccioná una subcategoría';
        return self::$alertas;
    }

    // Devuelve productos de una subcategoría
    public static function porSubcategoria($subcategoria_id)
    {
        return self::whereArray(['subcategoria_id' => $subcategoria_id]);
    }

    // Devuelve productos de una categoría completa (todas sus subcategorías)
    public static function porCategoria($categoria_id)
    {
        $categoria_id = (int) $categoria_id;
        $query = "SELECT p.* FROM productos p
                  INNER JOIN subcategorias s ON p.subcategoria_id = s.id
                  WHERE s.categoria_id = {$categoria_id}
                  ORDER BY p.id DESC";
        return self::consultarSQL($query);
    }

    /**
     * Construye el WHERE de un listado filtrable.
     * @param int[]    $subcategoria_ids IDs de subcategorías (alcance del listado)
     * @param string[] $marcas           Marcas seleccionadas (puede ser vacío)
     * @param float|null $precio_min
     * @param float|null $precio_max
     */
    private static function whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max) : string
    {
        $ids = array_map('intval', $subcategoria_ids);
        $where = ' WHERE subcategoria_id IN (' . (implode(',', $ids) ?: '0') . ')';

        if (!empty($marcas)) {
            $escapadas = array_map(fn($m) => "'" . self::$db->escape_string($m) . "'", $marcas);
            $where .= ' AND marca IN (' . implode(',', $escapadas) . ')';
        }
        if ($precio_min !== null) $where .= ' AND precio >= ' . (float) $precio_min;
        if ($precio_max !== null) $where .= ' AND precio <= ' . (float) $precio_max;

        return $where;
    }

    // Listado filtrado y paginado
    public static function filtrar($subcategoria_ids, $marcas, $precio_min, $precio_max, $por_pagina, $offset)
    {
        $query = 'SELECT * FROM productos'
               . self::whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max)
               . ' ORDER BY id DESC LIMIT ' . (int) $por_pagina . ' OFFSET ' . (int) $offset;
        return self::consultarSQL($query);
    }

    // Total de resultados del listado filtrado (para la paginación)
    public static function filtrarTotal($subcategoria_ids, $marcas, $precio_min, $precio_max) : int
    {
        $query = 'SELECT COUNT(*) FROM productos'
               . self::whereFiltros($subcategoria_ids, $marcas, $precio_min, $precio_max);
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return (int) array_shift($total);
    }

    // Marcas disponibles dentro del alcance del listado (para el sidebar)
    public static function marcasDisponibles($subcategoria_ids) : array
    {
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

    public static function buscar($termino)
    {
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
