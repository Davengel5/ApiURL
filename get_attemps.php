<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    if (empty($email)) {
        throw new Exception("Email requerido");
    }

    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT intentos, tipo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Usuario existe - devolver intentos
        echo json_encode([
            'attempts' => $user['intentos'],
            'is_premium' => $user['tipo'] === 'Premium'
        ]);
    } else {
        // Crear nuevo usuario si no existe
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, nombre, tipo, intentos) VALUES (?, ?, 'Free', 5)");
        $stmt->execute([$email, explode('@', $email)[0]]);
        
        echo json_encode([
            'attempts' => 5,
            'is_premium' => false
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>