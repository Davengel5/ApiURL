<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    
    echo json_encode([
        "exists" => $result['count'] > 0
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "exists" => false,
        "error" => $e->getMessage()
    ]);
}
?>