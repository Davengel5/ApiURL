<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 1. Conexión a MySQL
$db = new PDO(
    "mysql:host=".getenv('MYSQLHOST').";dbname=".getenv('MYSQLDATABASE'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// 2. Procesar POST
$input = json_decode(file_get_contents('php://input'), true);

// Validar input
if (empty($input['url']) || empty($input['user_id']) || empty($input['user_email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requieren: url, user_id y user_email']);
    exit;
}

// 3. Registrar usuario si no existe (upsert)
$stmt = $db->prepare("INSERT INTO users (user_id, email) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE email = VALUES(email)");
$stmt->execute([$input['user_id'], $input['user_email']]);

// 4. Verificar límite para usuarios free (5 URLs máx)
$stmt = $db->prepare("SELECT is_premium FROM users WHERE user_id = ?");
$stmt->execute([$input['user_id']]);
$user = $stmt->fetch();

if (!$user['is_premium']) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE user_id = ?");
    $stmt->execute([$input['user_id']]);
    if ($stmt->fetchColumn() >= 5) {
        http_response_code(403);
        echo json_encode(['error' => 'Límite de 5 URLs alcanzado. Actualiza a premium.']);
        exit;
    }
}

// 5. Generar slug único
do {
    $slug = substr(md5(uniqid()), 0, 6); // 6 caracteres
    $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
} while ($stmt->fetchColumn() > 0);

// 6. Guardar URL
try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO urls (slug, original_url, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$slug, $input['url'], $input['user_id']]);
    
    $shortUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $slug;
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'short_url' => $shortUrl,
        'is_premium' => $user['is_premium'] ?? false
    ]);

} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
}
?>