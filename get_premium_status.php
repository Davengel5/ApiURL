<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

$stmt = $pdo->prepare("SELECT tipo FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo json_encode([
    "is_premium" => $user && $user['tipo'] === 'Premium'
]);
?>