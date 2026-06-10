<?php

namespace Model;

class Producto extends ActiveRecord
{
    protected static $tabla = 'productos';
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'precio', 'stock', 'imagen', 'subcategoria_id', 'destacado'];

    public $id;
    public $nombre;
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

    public function __construct($args = [])
    {
        $this->id              = $args['id']              ?? null;
        $this->nombre          = $args['nombre']          ?? '';
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
        if (!isset($this->stock) || !filter_var($this->stock, FILTER_VALIDATE_INT) || $this->stock < 0)
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
        $query = "SELECT p.* FROM productos p
                  INNER JOIN subcategorias s ON p.subcategoria_id = s.id
                  WHERE s.categoria_id = {$categoria_id}
                  ORDER BY p.id DESC";
        return self::consultarSQL($query);
    }

    public static function buscar($termino)
    {
        $termino = self::$db->escape_string($termino);
        $query = "SELECT p.*, s.nombre AS subcategoria_nombre, c.nombre AS categoria_nombre
              FROM productos p
              INNER JOIN subcategorias s ON p.subcategoria_id = s.id
              INNER JOIN categorias c ON s.categoria_id = c.id
              WHERE p.nombre LIKE '%{$termino}%'
                 OR p.descripcion LIKE '%{$termino}%'
              ORDER BY p.nombre ASC
              LIMIT 8";
        return self::consultarSQL($query);
    }
}
