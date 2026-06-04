<?php
$db = mysqli_connect(
    'localhost',
    'root',
    '',
    'tienda_hardware'
);
 
if(!$db) {
    echo "Error: No se pudo conectar a MySQL. " . mysqli_connect_error();
    exit;
}
 
mysqli_set_charset($db, 'utf8mb4');