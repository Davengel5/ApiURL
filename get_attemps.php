<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(["error" => "Email requerido"]);
    exit;
}

$stmt = $pdo->prepare("SELECT intentos FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$result = $stmt->fetch();

if ($result) {
    echo json_encode(["attempts" => $result['intentos']]);
} else {
    echo json_encode(["attempts" => 0]); // O crear el usuario si no existe
}
?>