<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

if (isset($_POST['order_id'])) {
  $order_id = (int)$_POST['order_id'];
} else {
  $order_id = 0;
}
if ($order_id <= 0) {
  echo "<script>alert('ไม่พบคำสั่งซื้อ');window.location.href='/~cs6636089/GearZone/backend/my_orders.php';</script>";
  exit;
}

if (isset($_POST['product_id'])) {
  $product_id = (int)$_POST['product_id'];
} else {
  $product_id = 0;
}
if ($product_id <= 0) {
  echo "<script>alert('กรุณาเลือกสินค้า');history.back();</script>";
  exit;
}

if (isset($_POST['report_type'])) {
  $report_type = trim($_POST['report_type']);
} else {
  $report_type = '';
}

if (isset($_POST['description'])) {
  $description = trim($_POST['description']);
} else {
  $description = '';
}

if ($report_type === '' || $description === '') {
  echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');history.back();</script>";
  exit;
}

$chk = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$chk->execute([$order_id, $_SESSION['user_id']]);
$order = $chk->fetch();
if (!$order) {
  echo "<script>alert('ไม่พบคำสั่งซื้อของคุณ');window.location.href='/~cs6636089/GearZone/backend/my_orders.php';</script>";
  exit;
}

$chk2 = $pdo->prepare("SELECT COUNT(*) FROM Order_items WHERE order_id = ? AND product_id = ?");
$chk2->execute([$order_id, $product_id]);
$ok_in_order = $chk2->fetchColumn();

if (!$ok_in_order) {
  echo "<script>alert('สินค้าไม่ได้อยู่ในออเดอร์นี้');history.back();</script>";
  exit;
}

// บันทึกลง Reports
$ins = $pdo->prepare("INSERT INTO Reports (user_id, description, report_type, product_id, order_id)
VALUES (?, ?, ?, ?, ?)");
$ins->execute([$_SESSION['user_id'], $description, $report_type, $product_id, $order_id]);

echo "<script>alert('ส่งรายงานเรียบร้อยแล้ว');window.location.href='/~cs6636089/GearZone/backend/my_reports.php';</script>";
exit;
