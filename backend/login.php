<?php
session_start();
include "./connect.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['cart_flash'] = "กรุณากรอกอีเมลและรหัสผ่าน";
    header("Location: /~cs6636089/GearZone/frontend/login.html");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบรหัสผ่าน (plain text version)
if ($user && $user['password'] === $password) {

    // ออก session id ใหม่ทุกครั้งที่ login สำเร็จ (ป้องกัน reuse id เดิม)
    session_regenerate_id(true);

    // เก็บข้อมูลผู้ใช้ใน session
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];


    if ((int)$user['is_admin'] === 1) {
        header("Location: /~cs6636089/GearZone/frontend/admin.php");
    } else {
        header("Location: /~cs6636089/GearZone/index.html");
    }
    exit;

} else {
    $_SESSION['cart_flash'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    header("Location: /~cs6636089/GearZone/frontend/login.html");
    exit;
}
