<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT tipo, intentos FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $isPremium = $user['tipo'] === 'Premium';
        
        echo json_encode([
            'attempts' => $isPremium ? PHP_INT_MAX : $user['intentos'],
            'is_premium' => $isPremium
        ]);
    } else {
        // Usuario no existe, devolver valores por defecto
        echo json_encode([
            'attempts' => 5,
            'is_premium' => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'attempts' => 5,
        'is_premium' => false,
        'error' => $e->getMessage()
    ]);
}
?>