<?php
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Acortar URL
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['url']) || empty($data['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se proporcionó una URL.']);
        exit;
    }

    $url = $data['url'];
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);
    $userId = $data['user_id'] ?? null; // Línea 1 añadida: Obtener user_id

    $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)"); // Línea 2: Añadir user_id al INSERT
    $stmt->execute([$slug, $url, $userId]); // Línea 3: Pasar userId a execute

    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $shortUrl = "http://$host$path/$slug";

    echo json_encode([
        "slug" => $slug,
        "url" => $url,
        "short_url" => $shortUrl,
        "user_id" => $userId // Línea 4: Devolver userId en respuesta
    ]);

} elseif ($method === 'GET') {
    // Obtener URL por slug
    $slug = $_GET['slug'] ?? '';
    
    // Línea 5 opcional: Si quieres también el user_id al consultar
    $stmt = $pdo->prepare("SELECT url, user_id FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $resultado = $stmt->fetch();

    if ($resultado) {
        echo json_encode([
            "slug" => $slug,
            "url" => $resultado['url'],
            "user_id" => $resultado['user_id'] // Opcional
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Slug no encontrada.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}