<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || empty($data['email'])) {
        throw new Exception("Email requerido");
    }

    $pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("DELETE FROM urls WHERE user_id = ?");
    $stmt->execute([$data['email']]);
    
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE email = ?");
    $stmt->execute([$data['email']]);
    
    $pdo->commit();
    
    echo json_encode(["success" => true, "message" => "Cuenta eliminada"]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error en base de datos: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>