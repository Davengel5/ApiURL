<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Email requerido"]);
    exit;
}

try {
    // Obtener historial con paginación básica
    $page = max(1, $data['page'] ?? 1);
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Consulta para obtener URLs
    $stmt = $pdo->prepare("
        SELECT slug, url, created_at 
        FROM urls 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$email, $perPage, $offset]);
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para contar total
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
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>