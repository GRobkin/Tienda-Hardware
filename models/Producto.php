<?php
namespace Model;

class Producto extends ActiveRecord {
    protected static $tabla = 'productos';
    protected static $columnasDB = ['id','nombre','descripcion','precio','stock','imagen','categoria_id','destacado'];

    public $id;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $imagen;
    public $categoria_id;
    public $destacado;

    public function __construct($args = []) {
        $this->id           = $args['id']           ?? null;
        $this->nombre       = $args['nombre']       ?? '';
        $this->descripcion  = $args['descripcion']  ?? '';
        $this->precio       = $args['precio']       ?? 0;
        $this->stock        = $args['stock']        ?? 0;
        $this->imagen       = $args['imagen']       ?? 'default.webp';
        $this->categoria_id = $args['categoria_id'] ?? '';
        $this->destacado    = $args['destacado']    ?? 0;
    }

    public function validar() {
        if(!$this->nombre)       self::$alertas['error'][] = 'El nombre del producto es obligatorio';
        if(!$this->descripcion)  self::$alertas['error'][] = 'La descripción es obligatoria';
        if(!$this->precio || !is_numeric($this->precio) || $this->precio <= 0)
                                 self::$alertas['error'][] = 'El precio debe ser un número mayor a 0';
        if(!isset($this->stock) || !filter_var($this->stock, FILTER_VALIDATE_INT) || $this->stock < 0)
                                 self::$alertas['error'][] = 'El stock debe ser un número entero mayor o igual a 0';
        if(!$this->categoria_id || !filter_var($this->categoria_id, FILTER_VALIDATE_INT))
                                 self::$alertas['error'][] = 'Seleccioná una categoría';
        return self::$alertas;
    }
}
