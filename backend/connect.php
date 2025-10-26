<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=168DB_47;charset=utf8", "168DB47", "Re9Tti3v");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully" . "<br><br>"; 
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

   