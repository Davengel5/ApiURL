<?php
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Acortar URL
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['url']) || empty($data['url'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'No se proporcionó una URL.']);
        exit;
    }

    $url = $data['url'];
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);

    $stmt = $pdo->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $url]);

    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $shortUrl = "http://$host$path/$slug";

    echo json_encode([
        "slug" => $slug,
        "url" => $url,
        "short_url" => $shortUrl
    ]);

} elseif ($method === 'GET') {
    // Obtener URL por slug
    $slug = $_GET['slug'] ?? '';

    $stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $resultado = $stmt->fetch();

    if ($resultado) {
        echo json_encode(["slug" => $slug, "url" => $resultado['url']]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Slug no encontrada.']);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método no permitido.']);
}
