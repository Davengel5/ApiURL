<?php
#$pdo = new PDO('sqlite:urls.db');
$db = new PDO('sqlite:'.__DIR__.'/../urls.db');

$pdo->exec("CREATE TABLE IF NOT EXISTS urls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE,
    url TEXT
)");

echo "Base de datos y tabla 'urls' creadas correctamente.";
?>
