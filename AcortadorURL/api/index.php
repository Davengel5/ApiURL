<?php
header('Content-Type: application/json');
error_reporting(0);

// 1. Conexión a BD
try {
    #$db = new PDO('sqlite:urls.db');
    $db = new PDO('sqlite:'.__DIR__.'/../urls.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a BD']);
    exit;
}

// 2. Obtener slug de la RUTA (no de parámetro GET)
$request_uri = $_SERVER['REQUEST_URI'];
$slug = substr($request_uri, strrpos($request_uri, '/') + 1);

// Eliminar posibles parámetros adicionales (como ?foo=bar)
$slug = strtok($slug, '?');

if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['error' => 'Slug no proporcionado']);
    exit;
}

// 3. Buscar URL
try {
    $stmt = $db->prepare("SELECT url FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $url = $stmt->fetchColumn();
    
    if ($url) {
        header("Location: $url", true, 302);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'URL no encontrada']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
}
?>