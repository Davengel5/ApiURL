<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT intentos, tipo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $isPremium = $user['tipo'] === 'Premium';
        
        echo json_encode([
            'attempts' => $isPremium ? PHP_INT_MAX : $user['intentos'],
            'is_premium' => $isPremium
        ]);
    } else {
        // Usuario no existe, crear uno nuevo (free por defecto)
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, nombre, tipo, intentos) VALUES (?, ?, 'Free', 5)");
        $stmt->execute([$email, explode('@', $email)[0]]);
        
        echo json_encode([
            'attempts' => 5,
            'is_premium' => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>