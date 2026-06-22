<?php
// Controlador del buscador: recibe el término por GET y devuelve resultados en JSON para el autocompletado
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
                'imagen'   => imagen_producto($p), // URL lista (foto real o ícono)
                'categoria'=> $p->categoria_nombre ?? '',
                'url'      => '/producto?id=' . $p->id
            ];
        }, $productos);

        echo json_encode($resultado);
    }
}