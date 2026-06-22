<?php
// Modelo de las categorías principales del catálogo (ej: Procesadores, Monitores)
namespace Model;

class Categoria extends ActiveRecord {
    protected static $tabla = 'categorias';
    protected static $columnasDB = ['id','nombre','slug','descripcion'];

    public $id;
    public $nombre;
    public $slug;
    public $descripcion;

    public function __construct($args = []) {
        $this->id          = $args['id']          ?? null;
        $this->nombre      = $args['nombre']      ?? '';
        $this->slug        = $args['slug']        ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
    }

    public function validar() {
        if(!$this->nombre) self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->slug)   self::$alertas['error'][] = 'El slug es obligatorio';
        return self::$alertas;
    }

    // Busca una categoría por su slug
    public static function porSlug($slug) {
        return self::where('slug', $slug);
    }
}
