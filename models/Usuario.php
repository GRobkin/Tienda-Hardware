<?php
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id','nombre','apellido','email','password','token','confirmado','admin'];

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $password2;
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;
    public $admin;
    // Columna de solo lectura (no está en $columnasDB, no se escribe)
    public $creado_en;

    public function __construct($args = []) {
        $this->id         = $args['id']         ?? null;
        $this->nombre     = $args['nombre']     ?? '';
        $this->apellido   = $args['apellido']   ?? '';
        $this->email      = $args['email']      ?? '';
        $this->password   = $args['password']   ?? '';
        $this->password2  = $args['password2']  ?? '';
        $this->token      = $args['token']      ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->admin      = $args['admin']      ?? 0;
    }

    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        if(!$this->password) self::$alertas['error'][] = 'La contraseña no puede estar vacía';
        return self::$alertas;
    }

    public function validarCuenta() {
        if(!$this->nombre)   self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->apellido) self::$alertas['error'][] = 'El apellido es obligatorio';
        if(!$this->email)    self::$alertas['error'][] = 'El email es obligatorio';
        elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) self::$alertas['error'][] = 'Email no válido';
        if(!$this->password) self::$alertas['error'][] = 'La contraseña no puede estar vacía';
        elseif(strlen($this->password) < 6) self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
        if($this->password !== $this->password2) self::$alertas['error'][] = 'Las contraseñas no coinciden';
        return self::$alertas;
    }

    public function validarEmail() {
        if(!$this->email) self::$alertas['error'][] = 'El email es obligatorio';
        elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) self::$alertas['error'][] = 'Email no válido';
        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password) self::$alertas['error'][] = 'La contraseña no puede estar vacía';
        elseif(strlen($this->password) < 6) self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
        return self::$alertas;
    }

    public function comprobarPassword() : bool {
        return password_verify($this->password_actual, $this->password);
    }

    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken() : void {
        $this->token = bin2hex(random_bytes(16)); // 32 caracteres, impredecible
    }

    // Valida los datos editables del perfil (sin contraseña)
    public function validarPerfil() {
        if(!$this->nombre)   self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->apellido) self::$alertas['error'][] = 'El apellido es obligatorio';
        if(!$this->email)    self::$alertas['error'][] = 'El email es obligatorio';
        elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) self::$alertas['error'][] = 'Email no válido';
        return self::$alertas;
    }
}
