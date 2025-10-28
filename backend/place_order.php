<?php
session_start();
include "./connect.php";

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
} else {
  $user_id = 0;
}

if (isset($_SESSION['cart'])) {
  $cart = $_SESSION['cart'];
} else {
  $cart = [];
}

if (isset($_POST['shipping_address'])) {
  $addr = $_POST['shipping_address'];
} else {
  $addr = '';
}

if (isset($_POST['total_amount'])) {
  $total = $_POST['total_amount'];
} else {
  $total = 0;
}

if ($user_id == 0) {
  $_SESSION['cart_flash'] = "กรุณาเข้าสู่ระบบก่อนสั่งซื้อ";
  header("Location: /~cs6636089/GearZone/frontend/login.html"); exit;
}
if (empty($cart)) {
  $_SESSION['cart_flash'] = "ตะกร้าสินค้าว่าง";
  header("Location: /~cs6636089/GearZone/backend/cart_view.php"); exit;
}
if ($addr == '') {
  $_SESSION['cart_flash'] = "กรุณากรอกที่อยู่จัดส่ง";
  header("Location: /~cs6636089/GearZone/backend/checkout.php"); exit;
}

$stmt = $pdo->prepare("INSERT INTO Orders(user_id, payment_status, order_status, shipping_address, total_amount, order_day)
                       VALUES (?, 'Pending', 'Pending', ?, ?, CURRENT_DATE)");
$stmt->bindParam(1, $user_id);
$stmt->bindParam(2, $addr);
$stmt->bindParam(3, $total);
$stmt->execute();

$order_id = $pdo->lastInsertId();

foreach ($cart as $pid => $it) {
  $qty = $it['qty'];
  $price = $it['price'];
  $sub = $price * $qty;

  $stmt = $pdo->prepare("INSERT INTO Order_items(order_id, product_id, unit_price, total_price, quantity)
                         VALUES (?, ?, ?, ?, ?)");
  $stmt->bindParam(1, $order_id);
  $stmt->bindParam(2, $pid);
  $stmt->bindParam(3, $price);
  $stmt->bindParam(4, $sub);
  $stmt->bindParam(5, $qty);
  $stmt->execute();

  $stmt = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
  $stmt->bindParam(1, $qty);
  $stmt->bindParam(2, $pid);
  $stmt->execute();
}

$_SESSION['cart'] = [];
header("Location: /~cs6636089/GearZone/backend/order_success.php?order_id=" . $order_id);
exit;
