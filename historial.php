<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$idUsuario = $_GET['idUsuario'] ?? null;

if (!$idUsuario) {
    http_response_code(400);
    echo json_encode(["error" => "Se requiere idUsuario"]);
    exit;
}

$stmt = $pdo->prepare("SELECT slug, url, created_at FROM urls WHERE idUsuario = ? ORDER BY created_at DESC");
$stmt->execute([$idUsuario]);
$urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "count" => count($urls),
    "historial" => $urls
]);
?>