<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    // Verificar si el usuario es premium (ya sea en usuarios o en historial)
    $stmt = $pdo->prepare("SELECT u.intentos, u.tipo, 
                          (SELECT COUNT(*) FROM premium_historial WHERE user_email = u.email) as upgrades
                          FROM usuarios u WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Si tiene upgrades registrados o tipo Premium
        $isPremium = $user['tipo'] === 'Premium' || $user['upgrades'] > 0;
        
        echo json_encode([
            'attempts' => $isPremium ? PHP_INT_MAX : $user['intentos'],
            'is_premium' => $isPremium
        ]);
    } else {
        echo json_encode([
            'attempts' => 5,
            'is_premium' => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>