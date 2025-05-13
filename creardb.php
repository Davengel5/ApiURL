<?php
$dbPath = '/data/urls.db';
try {
    $db = new PDO("mysql:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY, slug TEXT UNIQUE, url TEXT)");
    file_put_contents('/data/init.log', "BD creada: " . date('Y-m-d H:i:s'));
} catch (PDOException $e) {
    file_put_contents('/data/error.log', "Error BD: " . $e->getMessage());
    exit(1); // Fuerza al script start.sh a fallar
}
?>