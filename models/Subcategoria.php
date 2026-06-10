<?php
namespace Model;

class Subcategoria extends ActiveRecord {
    protected static $tabla = 'subcategorias';
    protected static $columnasDB = ['id','nombre','slug','categoria_id','descripcion'];

    public $id;
    public $nombre;
    public $slug;
    public $categoria_id;
    public $descripcion;

    public function __construct($args = []) {
        $this->id           = $args['id']           ?? null;
        $this->nombre       = $args['nombre']       ?? '';
        $this->slug         = $args['slug']         ?? '';
        $this->categoria_id = $args['categoria_id'] ?? '';
        $this->descripcion  = $args['descripcion']  ?? '';
    }

    public function validar() {
        if(!$this->nombre)       self::$alertas['error'][] = 'El nombre es obligatorio';
        if(!$this->slug)         self::$alertas['error'][] = 'El slug es obligatorio';
        if(!$this->categoria_id || !filter_var($this->categoria_id, FILTER_VALIDATE_INT))
                                 self::$alertas['error'][] = 'Seleccioná una categoría';
        return self::$alertas;
    }

    // Devuelve todas las subcategorías de una categoría por su ID
    public static function porCategoria($categoria_id) {
        return self::whereArray(['categoria_id' => $categoria_id]);
    }

    // Busca una subcategoría por su slug
    public static function porSlug($slug) {
        return self::where('slug', $slug);
    }
}
