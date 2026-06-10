<?php
namespace Controllers;

use Model\Producto;

class BuscadorController {

    public static function buscar() {
        header('Content-Type: application/json');

        $termino = trim(s($_GET['q'] ?? ''));

        if(strlen($termino) < 2) {
            echo json_encode([]);
            return;
        }

        $productos = Producto::buscar($termino);

        $resultado = array_map(function($p) {
            return [
                'id'       => (int) $p->id,
                'nombre'   => $p->nombre,
                'precio'   => (float) $p->precio,
                'imagen'   => $p->imagen,
                'categoria'=> $p->categoria_nombre ?? '',
                'url'      => '/producto?id=' . $p->id
            ];
        }, $productos);

        echo json_encode($resultado);
    }
}