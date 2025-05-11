<?php
header('Content-Type: application/json');

require 'guardar.php'; // Reutilizamos el código de guardar

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // El contenido POST ya está manejado por guardar.php
} elseif ($method === 'GET') {
    // El contenido GET ya está manejado por index.php
    require 'index.php';
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>