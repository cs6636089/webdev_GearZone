<?php
session_start();
include "./connect.php";

if (isset($_POST['email'])) { 
    $email = $_POST['email']; 
} else { 
    $email = ''; 
}

if (isset($_POST['password'])) { 
    $password = $_POST['password']; 
} else { 
    $password = ''; 
}

if ($email == '' || $password == '') {
    $_SESSION['cart_flash'] = "กรุณากรอกอีเมลและรหัสผ่าน";
    header("Location: /~cs6636089/GearZone/frontend/login.html");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? AND password = ?");
$stmt->bindParam(1, $email);
$stmt->bindParam(2, $password);
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    // เก็บข้อมูลไว้ใน session
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];

    header("Location: /~cs6636089/GearZone/index.html");
    exit;
} else {
    $_SESSION['cart_flash'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    header("Location: /~cs6636089/GearZone/frontend/login.html");
    exit;
}
