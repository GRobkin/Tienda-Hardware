<?php

// Escapa HTML para evitar XSS
function s($html) : string {
    return htmlspecialchars($html ?? '');
}

// Debug rápido (solo en desarrollo)
function dd($variable) : void {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Verifica si hay una sesión activa (usuario logueado)
function is_auth() : bool {
    if(!isset($_SESSION)) session_start();
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

// Verifica si el usuario es admin
function is_admin() : bool {
    if(!isset($_SESSION)) session_start();
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

// Formatea precio en dólares (formato uruguayo: US$ 1.234,56)
function formatear_precio($precio) : string {
    return 'US$ ' . number_format((float)$precio, 2, ',', '.');
}

// Devuelve la cantidad total de items en el carrito
function total_carrito() : int {
    if(!isset($_SESSION)) session_start();
    return array_sum($_SESSION['carrito'] ?? []);
}

// Resalta la página activa en el menú
function pagina_activa($ruta) : string {
    $actual = $_SERVER['PATH_INFO'] ?? '/';
    return str_starts_with($actual, $ruta) ? 'activo' : '';
}

// ── CSRF ─────────────────────────────────────────────────────

// Token CSRF de la sesión (se crea una sola vez)
function csrf_token() : string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

// Campo oculto para formularios POST
function csrf_field() : string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Verifica el token recibido por POST
function csrf_check() : bool {
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

// ── Mensajes flash (sobreviven a un redirect) ────────────────

function flash($tipo, $mensaje) : void {
    $_SESSION['flash'][$tipo][] = $mensaje;
}

function obtener_flash() : array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

// ── Slugs ────────────────────────────────────────────────────

// "Placas Madre AM5" => "placas-madre-am5"
function generar_slug($texto) : string {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = strtr($texto, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

// ── Email ────────────────────────────────────────────────────

/**
 * Envía un email con mail() si el servidor está configurado y SIEMPRE
 * guarda una copia en /emails (bandeja de salida de desarrollo).
 * Devuelve true si mail() reportó envío real.
 */
function enviar_email($para, $asunto, $html) : bool {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Tienda Hardware <no-reply@tiendahardware.uy>\r\n";

    $enviado = @mail($para, $asunto, $html, $headers);

    // Bandeja de salida local: permite ver el email sin SMTP configurado
    $dir = __DIR__ . '/../emails';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $archivo = $dir . '/' . date('Ymd-His') . '-' . generar_slug($asunto) . '.html';
    file_put_contents($archivo, "<!-- Para: {$para} | Enviado por mail(): " . ($enviado ? 'si' : 'no') . " -->\n" . $html);

    return (bool) $enviado;
}

// Plantilla simple para los emails transaccionales
function plantilla_email($titulo, $cuerpo, $boton_texto, $boton_url) : string {
    return '
    <div style="font-family: system-ui, sans-serif; background:#f0f4ff; padding:32px 16px;">
        <div style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px; border:1px solid #e2e8f5;">
            <h1 style="font-size:20px; color:#0f172a; margin:0 0 12px;">' . s($titulo) . '</h1>
            <p style="font-size:14px; color:#64748b; line-height:1.6; margin:0 0 24px;">' . s($cuerpo) . '</p>
            <a href="' . s($boton_url) . '"
               style="display:inline-block; background:#2e47ff; color:#ffffff; text-decoration:none;
                      padding:12px 22px; border-radius:8px; font-size:14px; font-weight:600;">'
                . s($boton_texto) .
            '</a>
            <p style="font-size:12px; color:#94a3b8; margin:24px 0 0;">
                Si el botón no funciona, copiá este enlace: <br>' . s($boton_url) . '
            </p>
        </div>
    </div>';
}

// URL absoluta del sitio (para enlaces en emails)
function url_sitio($ruta = '') : string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return 'http://' . $host . $ruta;
}