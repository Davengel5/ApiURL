<?php
header('Content-Type: application/json');
error_reporting(0); // Desactivar mensajes de error en producción

// 1. Conexión segura a la base de datos
try {
    $dbPath = __DIR__.'/../urls.db';
    $db = new PDO('sqlite:'.$dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar permisos de escritura
    if (!is_writable($dbPath)) {
        throw new PDOException("La base de datos no tiene permisos de escritura");
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a BD: '.$e->getMessage()]);
    exit;
}

// 2. Obtener y validar datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Debe proporcionar una URL']);
    exit;
}

// 3. Validar formato de URL
if (!filter_var($input['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL no válida']);
    exit;
}

// 4. Generar slug único
do {
    $slug = substr(md5(uniqid(rand(), true)), 0, 6);
    $stmt = $db->prepare("SELECT COUNT(*) FROM urls WHERE slug = ?");
    $stmt->execute([$slug]);
} while ($stmt->fetchColumn() > 0);

// 5. Guardar en BD con transacción
try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $input['url']]);
    
    // Construir URL corta dinámica
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    $shortUrl = "$protocol$host$basePath/$slug";
    
    $db->commit();
    
    // 6. Respuesta JSON estructurada
    echo json_encode([
        'success' => true,
        'slug' => $slug,
        'short_url' => $shortUrl,
        'original_url' => $input['url']
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar URL: '.$e->getMessage()]);
}
?>