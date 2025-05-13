<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validar campos requeridos
    $required = ['nombre', 'email', 'tipo', 'intentos'];
    $missing = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Campos faltantes: " . implode(', ', $missing)
        ]);
        exit;
    }

    // Asignar valores
    $nombre = $data['nombre'];
    $email = $data['email'];
    $tipo = $data['tipo'];
    $intentos = $data['intentos'];

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, tipo, intentos)
                               VALUES (?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE tipo = VALUES(tipo)");
        $stmt->execute([$nombre, $email, $tipo, $intentos]);

        echo json_encode([
            "success" => true,
            "message" => "Usuario insertado o actualizado",
            "data" => [
                "nombre" => $nombre,
                "email" => $email,
                "tipo" => $tipo,
                "intentos" => $intentos
            ]
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}
?>
