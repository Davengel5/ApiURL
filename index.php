<?php
header('Content-Type: application/json');
error_reporting(0);

// 1. Conexión a BD - Ruta modificada para Railway
try {
    // Railway monta los volúmenes persistentes en /data
    $dbPath = '/data/urls.db'; 
    
    // Verificar si la base de datos existe
    if (!file_exists($dbPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Base de datos no inicializada']);
        exit;
    }
    
    $db = new PDO('sqlite:'.$dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a BD: '.$e->getMessage()]);
    exit;
}

// 2. Obtener slug de la RUTA - Adaptado para Nginx
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$slug = substr($request_uri, strrpos($request_uri, '/') + 1);

// Eliminar posibles parámetros adicionales y caracteres no válidos
$slug = preg_replace('/[^a-zA-Z0-9]/', '', strtok($slug, '?'));

if (empty($slug) || strlen($slug) != 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Slug no válido']);
    exit;
}

// 3. Buscar URL
try {
    $stmt = $db->prepare("SELECT url FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $url = $stmt->fetchColumn();
    
    if ($url) {
        // Validar URL antes de redireccionar
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            header("Location: $url", true, 302);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'URL almacenada no válida']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'URL no encontrada']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: '.$e->getMessage()]);
}
?>