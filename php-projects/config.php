<?php
// config.php
// Update these values to match your PostgreSQL setup
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'mydb';       // your DB name
$db_user = 'fulluser';   // or whatever user you want
$db_pass = 'yourpassword';

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
