<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$method = $_SERVER['REQUEST_METHOD'];
//Ya completa la funcionalidad creo
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validación básica de URL
    if (empty($data['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'URL requerida']);
        exit;
    }

    $url = $data['url'];
    $userId = $data['user_id'] ?? 'anonimo'; // Acepta cualquier identificador
    
    if ($userId !== 'anonimo') {
    $stmt = $pdo->prepare("SELECT intentos FROM usuarios WHERE email = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || $user['intentos'] <= 0) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes intentos disponibles']);
        exit;
    }
}
    // Generar slug
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);

    try {
        // Insertar en base de datos
        $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$slug, $url, $userId]);

        //Reducir intentos
        if ($userId !== 'anonimo') {
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos = intentos - 1 WHERE email = ?");
            $stmt->execute([$userId]);
            
            // Opcional: Obtener intentos restantes para la respuesta
            $stmt = $pdo->prepare("SELECT intentos FROM usuarios WHERE email = ?");
            $stmt->execute([$userId]);
            $remainingAttempts = $stmt->fetch()['intentos'];
}


        // Construir respuesta
        $host = $_SERVER['HTTP_HOST'];
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $shortUrl = "http://$host$path/$slug";

        echo json_encode([
            'success' => true,
            'slug' => $slug,
            'url' => $url,
            'short_url' => $shortUrl,
            'user_id' => $userId,
            'message' => 'URL creada exitosamente',
            'remaining_attempts' => $userId !== 'anonimo' ? $remainingAttempts : null
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en base de datos: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    // Endpoint original para redirección
    $slug = $_GET['slug'] ?? '';
    
    $stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $resultado = $stmt->fetch();

    if ($resultado) {
        echo json_encode(["slug" => $slug, "url" => $resultado['url']]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Slug no encontrado']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>