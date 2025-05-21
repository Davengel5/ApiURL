<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (empty($data['email'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Email requerido"]);
        exit;
    }
    $email = $data['email'];
    $stmt = $pdo->prepare("SHOW COLUMNS FROM usuarios LIKE 'fecha_upgrade'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    $sql = "UPDATE usuarios SET tipo = 'Premium'";
    if ($columnExists) {
        $sql .= ", fecha_upgrade = NOW()";
    }
    $sql .= " WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Usuario actualizado a Premium"]);
    } else {
        echo json_encode(["success" => false, "error" => "No se pudo actualizar el usuario"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>