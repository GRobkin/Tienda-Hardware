<?php
namespace Model;

class Orden extends ActiveRecord {
    protected static $tabla = 'ordenes';
    protected static $columnasDB = ['id','token','usuario_id','estado','total','nombre_pago','numero_tarjeta'];

    public $id;
    public $token;
    public $usuario_id;
    public $estado;
    public $total;
    public $nombre_pago;
    public $numero_tarjeta;

    public function __construct($args = []) {
        $this->id             = $args['id']             ?? null;
        $this->token          = $args['token']          ?? '';
        $this->usuario_id     = $args['usuario_id']     ?? '';
        $this->estado         = $args['estado']         ?? 'pendiente';
        $this->total          = $args['total']          ?? 0;
        $this->nombre_pago    = $args['nombre_pago']    ?? '';
        $this->numero_tarjeta = $args['numero_tarjeta'] ?? '';
    }

    public function validarPago() {
        if(!$this->nombre_pago)    self::$alertas['error'][] = 'El nombre del titular es obligatorio';
        if(!$this->numero_tarjeta) self::$alertas['error'][] = 'El número de tarjeta es obligatorio';
        elseif(!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $this->numero_tarjeta)))
                                   self::$alertas['error'][] = 'El número de tarjeta debe tener 16 dígitos';
        return self::$alertas;
    }
}
