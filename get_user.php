<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$email = $_GET['email'] ?? '';

if (!$email) {
    echo json_encode(["success" => false, "error" => "Falta el parámetro 'email'"]);
    exit;
}

$stmt = $pdo->prepare("SELECT intentos FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode(["success" => true, "intentos" => (int)$result['intentos']]);
} else {
    echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
}
