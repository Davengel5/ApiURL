<?php
// 1. Conexión a la base de datos (mantén tus credenciales)
$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 2. Obtener el slug de manera confiable
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$slug = '';

// Limpieza especial para Railway
if (strpos($requestUri, '//') === 0) {
    $slug = substr($requestUri, 2); // Quita las dos barras iniciales
} else {
    $slug = ltrim($requestUri, '/');
}

// Limpiar parámetros de query si existen
$slug = strtok($slug, '?');

// 3. Mostrar mensaje si no hay slug
if (empty($slug) || $slug === 'index.php') {
    echo "Bienvenido. Usa una URL corta para redirigir.";
    exit;
}

// 4. Buscar en la base de datos
$stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
$stmt->execute([$slug]);

if ($row = $stmt->fetch()) {
    // 5. Redirigir si existe
    header("Location: " . $row['url']);
    exit;
} else {
    // 6. Mostrar error si no existe
    http_response_code(404);
    echo "404 - URL no encontrada";
}
?>