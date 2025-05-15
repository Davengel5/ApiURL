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
    // Actualizar a Premium y guardar en tabla de historial
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE usuarios SET tipo = 'Premium', fecha_upgrade = NOW() WHERE email = ?");
    $stmt->execute([$email]);
    
    // Registrar en historial de upgrades
    $stmt = $pdo->prepare("INSERT INTO premium_historial (user_email, fecha) VALUES (?, NOW())");
    $stmt->execute([$email]);
    
    $pdo->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Usuario actualizado a Premium permanentemente"
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>