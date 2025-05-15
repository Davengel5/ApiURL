<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Conexión a la base de datos
$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // 2. Endpoint para acortar URLs (POST)
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['url']) || empty($data['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se proporcionó una URL.']);
        exit;
    }

    $url = $data['url'];
    $userId = $data['user_id'] ?? null; // ID del usuario desde la app
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);

    // 3. Insertar URL con relación al usuario
    $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$slug, $url, $userId]);

    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $shortUrl = "https://$host$path/$slug";

    echo json_encode([
        "slug" => $slug,
        "url" => $url,
        "short_url" => $shortUrl,
        "user_id" => $userId
    ]);

} elseif ($method === 'GET') {
    // 4. Dos tipos de endpoints GET:
    
    if (isset($_GET['slug'])) {
        // 4.1 Obtener URL original por slug (redirección)
        $slug = $_GET['slug'];
        $stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
        $stmt->execute([$slug]);
        $resultado = $stmt->fetch();

        if ($resultado) {
            echo json_encode([
                "slug" => $slug,
                "url" => $resultado['url']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Slug no encontrado.']);
        }
        
    } elseif (isset($_GET['user_id'])) {
        // 4.2 Obtener todas las URLs de un usuario (nuevo endpoint)
        $userId = $_GET['user_id'];
        
        // Paginación básica
        $page = max(1, $_GET['page'] ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Consulta principal
        $stmt = $pdo->prepare("SELECT slug, url, created_at FROM urls WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$userId, $perPage, $offset]);
        $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total para paginación
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM urls WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $total = $countStmt->fetch()['total'];
        
        echo json_encode([
            "data" => $urls,
            "meta" => [
                "total" => $total,
                "page" => $page,
                "per_page" => $perPage,
                "total_pages" => ceil($total / $perPage)
            ]
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros insuficientes. Usa ?slug= o ?user_id=']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}
?>