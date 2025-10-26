<?php
require_once DIR.'/config/session.php';
require_once DIR.'/connect.php';

// รับค่าจากฟอร์ม
$id = trim($_POST['email'] ?? '');   // ช่องในฟอร์มใช้ชื่อ email แต่ใส่ username ก็ได้
$pw = $_POST['password'] ?? '';

if ($id === '' || $pw === '') {
  header('Location: /~cs6636089/GearZone/frontend/login.html?error=empty');
  exit;
}

// หา user จาก email หรือ username
$sql = "SELECT user_id, username, password, email, first_name, last_name, phone, address, is_admin
        FROM Users WHERE email = :id OR username = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
  header('Location: /~cs6636089/GearZone/frontend/login.html?error=notfound');
  exit;
}

// ตรวจรหัสผ่านแบบพื้นฐาน:
// - ถ้าใน DB เป็น hash (เช่น เริ่มด้วย $2y$) → ใช้ password_verify
// - ถ้ายังเป็นข้อความธรรมดา → เทียบตรง ๆ (ชั่วคราวสำหรับงานพื้นฐาน)
$ok = false;
if (strpos($user['password'], '$2y$') === 0) {
  $ok = password_verify($pw, $user['password']);
} else {
  $ok = ($pw === $user['password']); // พื้นฐาน (แนะนำค่อยอัปเป็น hash ภายหลัง)
}

if (!$ok) {
  header('Location: /~cs6636089/GearZone/frontend/login.html?error=invalid');
  exit;
}

// ตั้งค่า session พื้นฐาน
$_SESSION['user'] = [
  'id'       => (int)$user['user_id'],
  'username' => $user['username'],
  'email'    => $user['email'],
  'first'    => $user['first_name'],
  'last'     => $user['last_name'],
  'phone'    => $user['phone'],
  'address'  => $user['address'],
  'is_admin' => (int)$user['is_admin'],
];

// ไปหน้าแรก (หรือหน้าผู้ดูแลถ้าเป็นแอดมิน)
// ...หลังตั้งค่า $_SESSION['user'] แล้ว
header('Location: ' . ((int)$user['is_admin'] === 1
  ? '/~cs6636089/GearZone/frontend/admin.php'   // <-- เปลี่ยนมาใช้หน้านี้
  : '/~cs6636089/GearZone/frontend/user.php'    // <-- และหน้านี้
));
exit;