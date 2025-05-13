<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// 1. Configuración de conexión MySQL con Railway
function getDatabaseConnection() {
    try {
        $db = new PDO(
            "mysql:host=".getenv('MYSQLHOST').";port=".getenv('MYSQLPORT').";dbname=".getenv('MYSQLDATABASE'),
            getenv('MYSQLUSER'),
            getenv('MYSQLPASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        return $db;
    } catch (PDOException $e) {
        error_log("Error de conexión MySQL: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'Error de conexión a la base de datos',
            'details' => 'Verifica las credenciales MySQL'
        ]);
        exit;
    }
}

// 2. Manejo de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Para preflight requests de CORS
}

$db = getDatabaseConnection();

// 3. Leer y validar input
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['url']) || empty($input['user_id']) || empty($input['user_email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requieren: url, user_id y user_email']);
    exit;
}

// 4. Registrar/actualizar usuario (UPSERT)
try {
    $stmt = $db->prepare("
        INSERT INTO users (user_id, email) 
        VALUES (:user_id, :email)
        ON DUPLICATE KEY UPDATE email = VALUES(email)
    ");
    $stmt->execute([
        ':user_id' => $input['user_id'],
        ':email' => $input['user_email']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar usuario: ' . $e->getMessage()]);
    exit;
}

// 5. Verificar límite para usuarios free
try {
    $stmt = $db->prepare("SELECT is_premium FROM users WHERE user_id = ?");
    $stmt->execute([$input['user_id']]);
    $user = $stmt->fetch();

    if (!$user['is_premium']) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE user_id = ?");
        $stmt->execute([$input['user_id']]);
        if ($stmt->fetchColumn() >= 5) {
            http_response_code(403);
            echo json_encode([
                'error' => 'Límite de 5 URLs alcanzado',
                'upgrade_url' => 'https://tu-dominio.com/upgrade'
            ]);
            exit;
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al verificar límite: ' . $e->getMessage()]);
    exit;
}

// 6. Generar slug único
do {
    $slug = substr(md5(uniqid().mt_rand()), 0, 6);
    $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
} while ($stmt->fetchColumn() > 0);

// 7. Guardar URL con transacción
try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("
        INSERT INTO urls (slug, original_url, user_id) 
        VALUES (:slug, :url, :user_id)
    ");
    $stmt->execute([
        ':slug' => $slug,
        ':url' => $input['url'],
        ':user_id' => $input['user_id']
    ]);
    
    $shortUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $slug;
    
    $db->commit();
    
    // 8. Respuesta exitosa
    echo json_encode([
        'success' => true,
        'short_url' => $shortUrl,
        'slug' => $slug,
        'user_id' => $input['user_id'],
        'is_premium' => $user['is_premium'] ?? false,
        'remaining_urls' => $user['is_premium'] ? 'unlimited' : 5 - ($stmt->rowCount() + 1)
    ]);

} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al guardar URL',
        'details' => $e->getMessage()
    ]);
}
?>