<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT tipo, fecha_upgrade FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($user = $stmt->fetch()) {
        $isPremium = $user['tipo'] === 'Premium';
        echo json_encode([
            "is_premium" => $isPremium,
            "fecha_upgrade" => $user['fecha_upgrade'],
            "status" => "success"
        ]);
    } else {
        echo json_encode([
            "is_premium" => false,
            "status" => "user_not_found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "is_premium" => false,
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>