<?php
session_start();
include "./connect.php";

$username   = $_POST['username'] ?? '';
$password   = $_POST['password'] ?? '';
$confirm_pw = $_POST['confirm-password'] ?? '';
$email      = $_POST['email'] ?? '';
$first      = $_POST['first_name'] ?? '';
$last       = $_POST['last_name'] ?? '';
$phone      = $_POST['phone'] ?? '';
$address    = $_POST['address'] ?? '';
$birthdate  = $_POST['birthdate'] ?? ''; 


if ($username == '' || $password == '' || $email == '') {
  echo "<script>alert('กรุณากรอกข้อมูลให้ครบ'); history.back();</script>";
  exit;
}

if ($password != $confirm_pw) {
  echo "<script>alert('รหัสผ่านไม่ตรงกัน'); history.back();</script>";
  exit;
}

$check = $pdo->prepare("SELECT * FROM Users WHERE username=? OR email=?");
$check->execute([$username, $email]);
if ($check->fetch()) {
  echo "<script>alert('ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว'); history.back();</script>";
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO Users (username, password, email, first_name, last_name, phone, address, birthdate, is_admin)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
");
$stmt->execute([$username, $password, $email, $first, $last, $phone, $address, $birthdate]);

echo "<script>alert('สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'); window.location.href='/~cs6636089/GearZone/frontend/login.html';</script>";
exit;
?>
