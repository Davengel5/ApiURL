<?php

// 1. Conectamos a la base de datos
	$pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 2. Obtenemos el slug que está en la URL
	//$slug = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); 

//2.5
	//if($slug === '')
	//{
	//	echo "Bienvenido al acortador. Proporciona una URL."
	//	exit;
	//}	


// 3. Buscamos en la base de datos
	//$stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
	//$stmt->execute([$slug]);
	
	//if ($row = $stmt->fetch()) 
	//{
		
	// 4. Si existe, redirigimos
    	//	header('Location: ' . $row['url']);
	//	exit;

	//} else {
		
	// 5. Si no existe, mostramos error
	//http_response_code(404);
    	//echo "URL no encontrada.";
	//}
	//
	
	$path = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");

if ($path === "" || $path === "index.php") {
    echo "Bienvenido. Usa una URL corta para redirigir.";
    exit;
}


$stmt = $pdo->prepare("SELECT url FROM urls WHERE slug = ?");
$stmt->execute([$path]);

if ($row = $stmt->fetch()) {
    header("Location: " . $row['url']);
    exit;
} else {
    http_response_code(404);
    echo "404 - URL no encontrada";
}
?>
