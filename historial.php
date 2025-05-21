<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $slug = $data['slug'] ?? '';
    $email = $data['email'] ?? '';

    if (empty($slug) || empty($email)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Slug y email requeridos"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM urls WHERE slug = ? AND user_id = ?");
        $stmt->execute([$slug, $email]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true,
                "message" => "URL eliminada del historial"
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "URL no encontrada o no pertenece al usuario"
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Error en la base de datos",
            "details" => $e->getMessage()
        ]);
    }
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Email requerido"]);
    exit;
}

try {
    $page = max(1, $data['page'] ?? 1);
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT slug, url, created_at 
        FROM urls 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bindValue(1, $email);
    $stmt->bindValue(2, (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM urls WHERE user_id = ?");
    $countStmt->execute([$email]);
    $total = $countStmt->fetch()['total'];

    echo json_encode([
        "success" => true,
        "data" => $urls,
        "meta" => [
            "total" => $total,
            "page" => $page,
            "per_page" => $perPage,
            "total_pages" => ceil($total / $perPage)
        ]
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