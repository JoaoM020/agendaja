<?php
// db.php
$host = 'localhost';
$db   = 'agenda_medicamentos';
$user = 'root';      // ajuste conforme seu MySQL
$pass = '';          // ajuste conforme seu MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
