<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT intentos FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch();

if ($result) {
    echo json_encode(["attempts" => $result['intentos']]);
} else {
    echo json_encode(["error" => "Usuario no encontrado"]);
}
?>