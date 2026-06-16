<?php

namespace Controllers;

use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use MVC\Router;

class PaginasController
{

    // Lee los filtros de marca y precio desde $_GET
    private static function leerFiltros(): array
    {
        $marcas = array_filter(array_map('trim', (array) ($_GET['marca'] ?? [])));
        $min = $_GET['precio_min'] ?? '';
        $max = $_GET['precio_max'] ?? '';
        return [
            'marcas'     => $marcas,
            'precio_min' => is_numeric($min) ? (float) $min : null,
            'precio_max' => is_numeric($max) ? (float) $max : null,
        ];
    }

    // Query string de los filtros activos (para que la paginación los conserve)
    private static function queryFiltros(array $filtros, array $extra = []): string
    {
        $params = $extra;
        if ($filtros['marcas'])              $params['marca']      = $filtros['marcas'];
        if ($filtros['precio_min'] !== null) $params['precio_min'] = $filtros['precio_min'];
        if ($filtros['precio_max'] !== null) $params['precio_max'] = $filtros['precio_max'];
        return http_build_query($params);
    }

    // Home
    public static function index(Router $router)
    {
        $destacados = array_slice(Producto::whereArray(['destacado' => 1]), 0, 4);
        $categorias = Categoria::all('ASC');
        $recientes  = Producto::get(8);

        foreach (array_merge($destacados, $recientes) as $producto) {
            $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
            if ($producto->subcategoria) {
                $producto->categoria = Categoria::find($producto->subcategoria->categoria_id);
            }
        }

        $router->render('paginas/index', [
            'titulo'     => 'Tienda de Hardware',
            'destacados' => $destacados,
            'categorias' => $categorias,
            'recientes'  => $recientes
        ]);
    }

    // Categoría: /categoria-producto/categoria?categoria=...
    public static function categoria(Router $router)
    {
        $slug = s($_GET['categoria'] ?? '');

        if (!$slug) {
            header('Location: /');
            exit;
        }

        $categoria = Categoria::porSlug($slug);
        if (!$categoria) {
            header('Location: /404');
            exit;
        }

        $subcategorias = Subcategoria::porCategoria($categoria->id);
        $alcance       = array_map(fn($s) => $s->id, $subcategorias);

        $filtros       = self::leerFiltros();
        $por_pagina    = 12;
        $pagina_actual = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $total         = Producto::filtrarTotal($alcance, $filtros['marcas'], $filtros['precio_min'], $filtros['precio_max']);
        $productos     = Producto::filtrar(
            $alcance,
            $filtros['marcas'],
            $filtros['precio_min'],
            $filtros['precio_max'],
            $por_pagina,
            ($pagina_actual - 1) * $por_pagina
        );

        // Nombre de subcategoría para el overline de cada tarjeta
        $subs_por_id = [];
        foreach ($subcategorias as $sub) $subs_por_id[$sub->id] = $sub;
        foreach ($productos as $producto) {
            $producto->subcategoria = $subs_por_id[$producto->subcategoria_id] ?? null;
        }

        $router->render('tienda/categoria', [
            'meta_descripcion' => $categoria->descripcion ?: "Comprá {$categoria->nombre} al mejor precio en Tienda Hardware",
            'titulo'        => $categoria->nombre,
            'categoria'     => $categoria,
            'subcategorias' => $subcategorias,
            'productos'     => $productos,
            'total'         => $total,
            'pagina_actual' => $pagina_actual,
            'total_paginas' => (int) ceil($total / $por_pagina),
            'marcas_disponibles' => Producto::marcasDisponibles($alcance),
            'filtros'       => $filtros,
            'query_filtros' => self::queryFiltros($filtros, ['categoria' => $categoria->slug])
        ]);
    }

    // Subcategoría: /categoria-producto/subcategoria?categoria=...&subcategoria=...
    public static function subcategoria(Router $router)
    {
        $slug_categoria    = s($_GET['categoria']    ?? '');
        $slug_subcategoria = s($_GET['subcategoria'] ?? '');

        if (!$slug_categoria || !$slug_subcategoria) {
            header('Location: /');
            exit;
        }

        $categoria    = Categoria::porSlug($slug_categoria);
        $subcategoria = Subcategoria::porSlug($slug_subcategoria);

        if (!$categoria || !$subcategoria) {
            header('Location: /404');
            exit;
        }

        if ($subcategoria->categoria_id != $categoria->id) {
            header('Location: /404');
            exit;
        }

        $subcategorias = Subcategoria::porCategoria($categoria->id);
        $alcance       = [$subcategoria->id];

        $filtros       = self::leerFiltros();
        $por_pagina    = 12;
        $pagina_actual = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $total         = Producto::filtrarTotal($alcance, $filtros['marcas'], $filtros['precio_min'], $filtros['precio_max']);
        $productos     = Producto::filtrar(
            $alcance,
            $filtros['marcas'],
            $filtros['precio_min'],
            $filtros['precio_max'],
            $por_pagina,
            ($pagina_actual - 1) * $por_pagina
        );

        $router->render('tienda/subcategoria', [
            'meta_descripcion' => "{$subcategoria->nombre} en {$categoria->nombre} — Tienda Hardware",
            'titulo'        => $subcategoria->nombre . ' — ' . $categoria->nombre,
            'categoria'     => $categoria,
            'subcategoria'  => $subcategoria,
            'subcategorias' => $subcategorias,
            'productos'     => $productos,
            'total'         => $total,
            'pagina_actual' => $pagina_actual,
            'total_paginas' => (int) ceil($total / $por_pagina),
            'marcas_disponibles' => Producto::marcasDisponibles($alcance),
            'filtros'       => $filtros,
            'query_filtros' => self::queryFiltros($filtros, [
                'categoria' => $categoria->slug,
                'subcategoria' => $subcategoria->slug
            ])
        ]);
    }

    // Detalle de producto
    public static function producto(Router $router)
    {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /');
            exit;
        }

        $producto = Producto::find($id);
        if (!$producto) {
            header('Location: /404');
            exit;
        }

        $producto->subcategoria = Subcategoria::find($producto->subcategoria_id);
        $producto->categoria    = $producto->subcategoria
            ? Categoria::find($producto->subcategoria->categoria_id)
            : null;

        $relacionados = Producto::consultarSQL(
            "SELECT * FROM productos WHERE subcategoria_id = {$producto->subcategoria_id} AND id != {$id} LIMIT 4"
        );

        $router->render('tienda/producto', [
            'meta_descripcion' => mb_substr($producto->nombre . ' — ' . $producto->descripcion, 0, 155),
            'titulo'       => $producto->nombre,
            'producto'     => $producto,
            'relacionados' => $relacionados
        ]);
    }

    // Sobre
    public static function sobre(Router $router)
    {
        $router->render('paginas/sobre', ['titulo' => 'Sobre nosotros']);
    }

    // Garantía
    public static function garantia(Router $router)
    {
        $router->render('paginas/garantia', ['titulo' => 'Garantía']);
    }

    // Contacto
    public static function contacto(Router $router)
    {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                $alertas['error'][] = 'La sesión expiró, intentá de nuevo';
                $router->render('paginas/contacto', ['titulo' => 'Contacto', 'alertas' => $alertas]);
                return;
            }
            $nombre  = s($_POST['nombre']  ?? '');
            $email   = s($_POST['email']   ?? '');
            $mensaje = s($_POST['mensaje'] ?? '');

            if (!$nombre)  $alertas['error'][] = 'El nombre es obligatorio';
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
                $alertas['error'][] = 'El email no es válido';
            if (!$mensaje) $alertas['error'][] = 'El mensaje es obligatorio';

            if (empty($alertas)) {
                $alertas['exito'][] = '¡Mensaje enviado! Te responderemos pronto';
            }
        }

        $router->render('paginas/contacto', [
            'titulo'  => 'Contacto',
            'alertas' => $alertas
        ]);
    }

    // 404
    public static function error(Router $router)
    {
        http_response_code(404);
        $router->render('paginas/error', ['titulo' => 'Página no encontrada']);
    }
}
