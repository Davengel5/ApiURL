<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// 1. Configuración de logs para debug
file_put_contents('/data/debug.log', "\n[" . date('Y-m-d H:i:s') . "] Inicio de guardar.php\n", FILE_APPEND);

// 2. Conexión a BD con manejo robusto de errores
$dbPath = '/data/urls.db';
file_put_contents('/data/debug.log', "Ruta de BD: $dbPath\n", FILE_APPEND);

try {
    // Verificar permisos del archivo
    if (file_exists($dbPath)) {
        file_put_contents('/data/debug.log', "Permisos actuales: " . decoct(fileperms($dbPath)) . "\n", FILE_APPEND);
        chmod($dbPath, 0666); // Asegurar permisos de escritura
    }

    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla si no existe (con manejo de concurrencia)
    $db->exec("CREATE TABLE IF NOT EXISTS urls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE,
        url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    file_put_contents('/data/error.log', "Error de conexión: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// 3. Procesar input JSON (compatible con tu app Android)
$input = json_decode(file_get_contents('php://input'), true);

// Fallback para datos de formulario (por si acaso)
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

file_put_contents('/data/debug.log', "Datos recibidos: " . print_r($input, true) . "\n", FILE_APPEND);

if (empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'URL no proporcionada']);
    exit;
}

// 4. Validación de URL
if (!filter_var($input['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL no válida']);
    exit;
}

// 5. Generación de slug único
$maxAttempts = 5;
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
    echo json_encode(['error' => 'No se pudo generar un slug único']);
    exit;
}

// 6. Transacción para insertar
try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $input['url']]);
    
    $shortUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $slug;
    
    $db->commit();
    
    // 7. Respuesta compatible con tu app Android
    echo json_encode([
        'short_url' => $shortUrl,  // Campo que tu app espera
        'slug' => $slug,
        'original_url' => $input['url'],
        'status' => 'success'      // Compatibilidad con tus pruebas anteriores
    ]);
    
    file_put_contents('/data/debug.log', "URL acortada creada: $shortUrl\n", FILE_APPEND);

} catch (PDOException $e) {
    $db->rollBack();
    file_put_contents('/data/error.log', "Error al guardar: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la URL']);
}
?>