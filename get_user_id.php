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

// Buscar usuario existente
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(["id" => $user['id']]);
} else {
    // Crear nuevo usuario si no existe
    $stmt = $pdo->prepare("INSERT INTO usuarios (email, nombre, intentos) VALUES (?, ?, ?)");
    $stmt->execute([$email, "Usuario " . substr($email, 0, strpos($email, '@')), 5]);
    
    echo json_encode([
        "id" => $pdo->lastInsertId(),
        "message" => "Nuevo usuario creado"
    ]);
}
?>