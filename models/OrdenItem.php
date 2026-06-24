<?php
// Representa una línea de una orden: qué producto, cuántas unidades y a qué precio se compró
namespace Model;

class OrdenItem extends ActiveRecord {
    protected static $tabla = 'orden_items';
    protected static $columnasDB = ['id','orden_id','producto_id','cantidad','precio_unitario'];

    public $id;
    public $orden_id;
    public $producto_id;
    public $cantidad;
    public $precio_unitario;
    public $producto;

    public function __construct($args = []) {
        $this->id              = $args['id']              ?? null;
        $this->orden_id        = $args['orden_id']        ?? '';
        $this->producto_id     = $args['producto_id']     ?? '';
        $this->cantidad        = $args['cantidad']        ?? 1;
        $this->precio_unitario = $args['precio_unitario'] ?? 0;
    }
}
