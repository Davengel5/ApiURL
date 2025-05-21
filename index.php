<?php
$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$slug = '';

if (strpos($requestUri, '//') === 0) {
    $slug = substr($requestUri, 2);
} else {
    $slug = ltrim($requestUri, '/');
}

$slug = strtok($slug, '?');

if (empty($slug) || $slug === 'index.php') {
    echo "Bienvenido. Usa una URL corta para redirigir.";
    exit;
}

$stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
$stmt->execute([$slug]);

if ($row = $stmt->fetch()) {
    header("Location: " . $row['url']);
    exit;
} else {
    http_response_code(404);
    echo "404 - URL no encontrada";
}
?>