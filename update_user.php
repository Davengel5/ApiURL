<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(["success" => false, "error" => "Email requerido"]);
    exit;
}

try {
    // Actualizar a Premium permanentemente
    $stmt = $pdo->prepare("UPDATE usuarios SET tipo = 'Premium', fecha_upgrade = NOW() WHERE email = ?");
    $stmt->execute([$email]);
    
    echo json_encode([
        "success" => true,
        "message" => "Usuario actualizado a Premium"
    ]);
    
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>