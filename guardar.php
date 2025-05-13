<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Conexión a la base de datos con manejo de errores mejorado
try {
    $dbPath = '/data/urls.db'; // Ruta persistente en Railway
    
    // Verificar/Crear base de datos si no existe
    if (!file_exists($dbPath)) {
        file_put_contents($dbPath, '');
        chmod($dbPath, 0666);
    }
    
    $db = new PDO('sqlite:'.$dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla con índices optimizados
    $db->exec("CREATE TABLE IF NOT EXISTS urls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE,
        url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_slug ON urls(slug)");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
    exit;
}

// 2. Manejar preflight OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 3. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// 4. Leer y validar input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

if (empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'URL no proporcionada']);
    exit;
}

// 5. Validar formato de URL
$url = filter_var($input['url'], FILTER_VALIDATE_URL);

if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'URL no válida']);
    exit;
}

// 6. Generar slug único (3 intentos)
$maxAttempts = 3;
$attempt = 0;
$slug = null;

do {
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);
    $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
    $attempt++;
} while ($stmt->fetchColumn() > 0 && $attempt < $maxAttempts);

if ($attempt >= $maxAttempts) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo generar URL única']);
    exit;
}

// 7. Guardar en base de datos con transacción
try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $url]);
    
    $shortUrl = "https://{$_SERVER['HTTP_HOST']}/$slug";
    
    $db->commit();
    
    // 8. Respuesta exitosa
    echo json_encode([
        'success' => true,
        'short_url' => $shortUrl,  // Campo que espera la app Android
        'slug' => $slug,
        'original_url' => $url,
        'info' => 'URL acortada creada'
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al guardar URL',
        'details' => $e->getMessage()
    ]);
    
    // Log del error (útil en Railway)
    file_put_contents('php://stderr', "DB Error: ".$e->getMessage()."\n");
}
?>