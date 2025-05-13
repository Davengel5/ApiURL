<?php
    // 1. Conectamos a la base de datos

    $pdo = new PDO('mysql:host=mysql.railway.internal;dbname=railway;charset=utf8mb4', 'root', 'PmbYEyrQWIIItorYmqhWMsuaRKHACDcc');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1.5 Si no existe, la crea automáticamente.
    //if (!isset($_POST['url'])) {
    //    echo "No enviaste ninguna URL.";
    //    exit;
    //}
<<<<<<< HEAD
    //$datos = json_decode(file_get_contents("php://input"), true);
=======
    $datos = json_decode(file_get_contents("php://input"), true);
>>>>>>> 2efd379949782690c887148094471087fd715a05

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

    // 2. Recibimos la URL que el usuario mandó
    $url = $_POST['url']; // (viene del formulario)
    $url = $datos['url'];


    // 3. Generamos un slug único (código corto)
    $slug = substr(md5(uniqid(rand(), true)), 0, 6); 
    // Explicación: md5 genera un hash basado en un número aleatorio y la hora, luego cortamos los primeros 6 caracteres para que sea corto.

    // 4. Guardamos en la base de datos
    $stmt = $pdo->prepare("INSERT INTO urls (slug, url) VALUES (?, ?)");
    $stmt->execute([$slug, $url]);

    // Mostrar la URL acortada
    $dominio = $_SERVER['HTTP_HOST'];  // ejemplo: localhost
    $rutaBase = dirname($_SERVER['PHP_SELF']);  // ejemplo: /operacioncardoso

    // Asegurarse que no haya barra doble
    $rutaBase = rtrim($rutaBase, '/');

    echo "Tu URL corta es: <a href='http://$dominio$rutaBase/$slug'>http://$dominio$rutaBase/$slug</a>";
?>
