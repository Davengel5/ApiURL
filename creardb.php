<?php
if (!file_exists(__DIR__.'/urls.db')) {
    $db = new PDO('sqlite:'.__DIR__.'/urls.db');
    $db->exec("CREATE TABLE urls (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, url TEXT)");
    echo "Base de datos creada correctamente";
} else {
    echo "La base de datos ya existe";
}
?>