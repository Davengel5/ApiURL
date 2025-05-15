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
    
    // Primero verifica si la columna existe
    $stmt = $pdo->prepare("SHOW COLUMNS FROM usuarios LIKE 'fecha_upgrade'");
    $stmt->execute();
    $columnExists = $stmt->fetch();

    // Construye la consulta SQL dinámicamente
    $sql = "UPDATE usuarios SET tipo = 'Premium'";
    if ($columnExists) {
        $sql .= ", fecha_upgrade = NOW()";
    }
    $sql .= " WHERE email = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    echo json_encode([
        "success" => true,
        "message" => "Usuario actualizado a Premium",
        "email" => $email
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error en la base de datos",
        "details" => $e->getMessage()
    ]);
}
?>