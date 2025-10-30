<?php
session_start();

// ล้างตัวแปรใน session
$_SESSION = [];

// ลบคุกกี้ PHPSESSID ออกจากเบราว์เซอร์
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_unset();   // ล้างตัวแปร session ทั้งหมด
session_destroy(); // ทำลาย session ทั้งระบบ

header("Location: /~cs6636089/GearZone/index.html");
exit;
