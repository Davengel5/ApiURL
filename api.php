<?php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Leer el cuerpo de la solicitud (JSON)
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['url'])) {
        // Aquí puedes procesar la URL (ejemplo: guardarla en DB o acortarla)
        $url = $input['url'];
        echo json_encode(['status' => 'success', 'url' => $url]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'URL no proporcionada']);
    }
} elseif ($method === 'GET') {
    require 'index.php'; // Manejo de GET (ya funciona)
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>