<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

// Manejo de solicitudes OPTIONS para CORS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    if ($method === 'POST') {
        // Leer el input JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['url'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Debe proporcionar una URL válida en formato JSON',
                'example' => ['url' => 'https://ejemplo.com']
            ]);
            exit;
        }

        // Validar URL
        if (!filter_var($input['url'], FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'URL no válida',
                'received' => $input['url']
            ]);
            exit;
        }

        // Procesar con guardar.php
        require __DIR__.'/guardar.php';
        
    } elseif ($method === 'GET') {
        // Manejo de redirecciones (index.php ya maneja esto)
        require __DIR__.'/index.php';
        
    } else {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Método no permitido',
            'allowed_methods' => ['GET', 'POST']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor',
        'details' => $e->getMessage()
    ]);
    
    // Log del error (útil para depuración)
    file_put_contents(__DIR__.'/api_errors.log', 
        date('[Y-m-d H:i:s]')." Error: ".$e->getMessage()."\n".$e->getTraceAsString()."\n\n", 
        FILE_APPEND);
}
?>