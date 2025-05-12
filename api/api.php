<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Permite POST
header('Access-Control-Allow-Origin: *'); // Permite CORS

// Conexión a SQLite
$db = new PDO('sqlite:' . __DIR__ . '/urls.db');

// Manejo de CORS para peticiones OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Tu lógica original aquí
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    // ... resto de tu código POST
} elseif ($method === 'GET') {
    // ... tu código GET
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>