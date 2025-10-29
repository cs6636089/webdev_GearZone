<?php
// เปิด strict mode และใช้คุกกี้เท่านั้น
ini_set('session.use_strict_mode', '1'); //PHP จะ ปฏิเสธ การใช้ session id ที่ถูกปลอม
ini_set('session.use_only_cookies', '1');

$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

session_set_cookie_params([
  'lifetime' => 3600, // 1 ชั่วโมง
  'path' => '/',
  'domain' => '',
  'secure' => $secure,
  'httponly' => true,
  'samesite' => 'Lax',
]);


if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
