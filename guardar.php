<?php

    $pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'fvnJSMGrEiLaBGmOKQdhpAQgamPtRVat');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!isset($datos['url'])) {
        echo "No enviaste ninguna URL.";
        exit;
    }

    elseif (isset($_POST['url'])) {
        $url = $_POST['url'];
    }
    else {
        echo "No enviaste ninguna URL.";
        exit;
    }

    $url = $_POST['url'];
    $url = $datos['url'];

    $slug = substr(md5(uniqid(rand(), true)), 0, 6); 

    $stmt = $pdo->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $url]);

    $dominio = $_SERVER['HTTP_HOST'];
    $rutaBase = dirname($_SERVER['PHP_SELF']);

    $rutaBase = rtrim($rutaBase, '/');

    echo "Tu URL corta es: <a href='http://$dominio$rutaBase/$slug'>http://$dominio$rutaBase/$slug</a>";
?>
