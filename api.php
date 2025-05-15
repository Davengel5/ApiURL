<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['url']) || empty($data['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'URL requerida']);
        exit;
    }

    if (!isset($data['user_id']) || empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        exit;
    }

    $url = $data['url'];
    $userId = (int)$data['user_id'];
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);

    // Verificar que el usuario existe
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmtCheck->execute([$userId]);
    
    if (!$stmtCheck->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    // Insertar URL
    $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$slug, $url, $userId]);

    // Actualizar intentos (nuevo)
    $stmtAttempts = $pdo->prepare("UPDATE usuarios SET intentos = intentos - 1 WHERE id = ? AND intentos > 0");
    $stmtAttempts->execute([$userId]);

    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $shortUrl = "http://$host$path/$slug";

    echo json_encode([
        "success" => true,
        "slug" => $slug,
        "url" => $url,
        "short_url" => $shortUrl,
        "user_id" => $userId
    ]);

} elseif ($method === 'GET') {
    // ... (mantén tu código existente para GET)
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>