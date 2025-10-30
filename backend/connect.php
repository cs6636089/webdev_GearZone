<?php

$host = 'localhost';
$db   = '168DB_47';
$user = '168DB47';     // user MySQL
$pass = 'Re9Tti3v';     // รหัสผ่าน MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DB Error: ' . $e->getMessage());
}
