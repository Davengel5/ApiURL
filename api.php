<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Verificación más robusta de la URL
    if (!isset($data['url']) || empty(trim($data['url']))) {
        http_response_code(400);
        echo json_encode(['error' => 'URL requerida']);
        exit;
    }

    // Verificar que sea una URL válida
    $url = filter_var(trim($data['url']), FILTER_VALIDATE_URL);
    if ($url === false) {
        http_response_code(400);
        echo json_encode(['error' => 'URL no válida']);
        exit;
    }

    // Verificar user_id
    if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario no válido']);
        exit;
    }

    $userId = (int)$data['user_id'];
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);

    try {
        $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$slug, $url, $userId]);

        $host = $_SERVER['HTTP_HOST'];
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $shortUrl = "https://$host$path/$slug";

        echo json_encode([
            "success" => true,
            "slug" => $slug,
            "url" => $url,
            "short_url" => $shortUrl,
            "user_id" => $userId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>