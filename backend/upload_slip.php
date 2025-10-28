<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}


if (isset($_POST['order_id'])) {
  $order_id = $_POST['order_id'];
} else {
  $order_id = 0;
}

if ($order_id <= 0) {
  echo "ไม่พบคำสั่งซื้อ";
  header("Location: /~cs6636089/GearZone/backend/my_orders.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
  echo "ไม่พบคำสั่งซื้อนี้";
  exit;
}

if (!isset($_FILES['slip']) || $_FILES['slip']['error'] != UPLOAD_ERR_OK) {
  echo "กรุณาอัปโหลดไฟล์สลิปให้ถูกต้อง";
  exit;
}

if ($_FILES['slip']['size'] > 5 * 1024 * 1024) {
  echo "ไฟล์มีขนาดใหญ่เกินไป (เกิน 5MB)";
  exit;
}

$allowed = ['image/jpeg', 'image/png', 'application/pdf'];
$ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array($ext, $allowed_ext)) {
  echo "อนุญาตเฉพาะไฟล์ JPG, PNG หรือ PDF เท่านั้น";
  exit;
}


// สร้างโฟลเดอร์เก็บไฟล์สลิป (ถ้ายังไม่มี)
$folder = __DIR__ . "/../assets/slips";
if (!is_dir($folder)) {
  mkdir($folder, 0775, true);
}

// ตั้งชื่อไฟล์
$ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
$new_name = "order_" . $order_id . "_" . time() . "." . $ext;
$path_save = $folder . "/" . $new_name;
$path_db = "/~cs6636089/GearZone/assets/slips/" . $new_name;

if (move_uploaded_file($_FILES['slip']['tmp_name'], $path_save)) {
  // อัปเดตในฐานข้อมูล
  $update = $pdo->prepare("UPDATE Orders SET payment_status='Pending Review', payment_slip=? WHERE order_id=?");
  $update->execute([$path_db, $order_id]);

  echo "<script>alert('อัปโหลดสลิปเรียบร้อยแล้ว รอตรวจสอบจากแอดมิน'); 
      window.location.href='/~cs6636089/GearZone/backend/my_orders.php';
      </script>";
} else {
  echo "ไม่สามารถบันทึกไฟล์ได้";
}
?>
