<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    error_log("Datos recibidos: " . print_r($data, true));
    if (empty($data['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'URL requerida']);
        exit;
    }
    $url = $data['url'];
    $userEmail = $data['user_id'] ?? 'anonimo';
    try {
        $stmt = $pdo->prepare("SELECT id, intentos, tipo FROM usuarios WHERE email = ?");
        $stmt->execute([$userEmail]);
        $user = $stmt->fetch();
        if (!$user && $userEmail !== 'anonimo') {
            $stmt = $pdo->prepare("INSERT INTO usuarios (email, nombre, tipo, intentos) VALUES (?, ?, 'Free', 5)");
            $stmt->execute([$userEmail, explode('@', $userEmail)[0]]);
            $user = ['intentos' => 5, 'tipo' => 'Free'];
        }
        if ($userEmail !== 'anonimo' && $user['tipo'] !== 'Premium' && $user['intentos'] <= 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Límite de intentos alcanzado']);
            exit;
        }
        $slug = substr(md5(uniqid(rand(), true)), 0, 6);
        $stmt = $pdo->prepare("INSERT INTO urls (slug, url, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$slug, $url, $userEmail]);
        if ($userEmail !== 'anonimo' && $user['tipo'] !== 'Premium') {
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos = intentos - 1 WHERE email = ?");
            $stmt->execute([$userEmail]);
            $remainingAttempts = $user['intentos'] - 1;
        } else {
            $remainingAttempts = $user['intentos'] ?? 0;
        }
        $host = $_SERVER['HTTP_HOST'];
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $shortUrl = "https://$host$path/$slug";
        echo json_encode([
            'success' => true,
            'short_url' => $shortUrl,
            'remaining_attempts' => $remainingAttempts,
            'is_premium' => ($user['tipo'] ?? 'Free') === 'Premium',
            'message' => 'URL creada exitosamente'
        ]);
    } catch (PDOException $e) {
        error_log("Error en BD: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error en base de datos']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>