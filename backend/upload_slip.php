<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
  exit("ไม่พบคำสั่งซื้อ");
}

$stmt = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
  exit("ไม่พบคำสั่งซื้อนี้");
}

if (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
  exit("กรุณาอัปโหลดไฟล์สลิปให้ถูกต้อง");
}
if ($_FILES['slip']['size'] > 5 * 1024 * 1024) {
  exit("ไฟล์มีขนาดใหญ่เกินไป (เกิน 5MB)");
}

$ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array($ext, $allowed_ext, true)) {
  exit("อนุญาตเฉพาะไฟล์ JPG, PNG หรือ PDF เท่านั้น");
}


$projectRoot = dirname(__DIR__);                
$folder      = $projectRoot . "/assets/slips"; 


if (!is_dir($folder)) {
  if (!mkdir($folder, 0755, true)) {
    exit("สร้างโฟลเดอร์ไม่สำเร็จ: $folder");
  }
}
if (!is_writable($folder)) {
  exit("โฟลเดอร์ไม่อนุญาตให้เขียน: $folder (กรุณาตั้งสิทธิ์ให้เขียนได้)");
}

if (!is_uploaded_file($_FILES['slip']['tmp_name'])) {
  exit("ไฟล์ชั่วคราวไม่ถูกต้อง: ".$_FILES['slip']['tmp_name']);
}


$new_name = "order_" . $order_id . "_" . time() . "." . $ext;
$path_save = $folder . "/" . $new_name;  
$path_db   = "/~cs6636089/GearZone/assets/slips/" . $new_name; 


if (move_uploaded_file($_FILES['slip']['tmp_name'], $path_save)) {
  @chmod($path_save, 0644);
  $update = $pdo->prepare("UPDATE Orders SET payment_status='Pending Review', payment_slip=? WHERE order_id=?");
  $update->execute([$path_db, $order_id]);

  echo "<script>
    alert('อัปโหลดสลิปเรียบร้อยแล้ว รอตรวจสอบจากแอดมิน');
    window.location.href='/~cs6636089/GearZone/backend/my_orders.php';
  </script>";
  exit;
} else {
  exit("ไม่สามารถบันทึกไฟล์ได้ (Path: $path_save)");
}
