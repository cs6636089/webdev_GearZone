<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

$user_id = $_SESSION['user_id'];

$username = trim($_POST['username']); //ตัดช่องว่าง หัวท้าย
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$new_password = trim($_POST['new_password'] ?? '');

if ($new_password !== '') {
  $update = $pdo->prepare("UPDATE Users SET username=?, phone=?, address=?, password=? WHERE user_id=?");
  $update->execute([$username, $phone, $address, $new_password, $user_id]);
} else {
  $update = $pdo->prepare("UPDATE Users SET username=?, phone=?, address=? WHERE user_id=?");
  $update->execute([$username, $phone, $address, $user_id]);
}

echo "<script>
  alert('บันทึกการแก้ไขเรียบร้อยแล้ว');
  window.location.href='/~cs6636089/GearZone/index.html';
</script>";
exit;
