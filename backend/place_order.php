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
    $addr = trim($_POST['shipping_address']);
} else {
    $addr = '';
}


if ($user_id == 0) {
  $_SESSION['cart_flash'] = "กรุณาเข้าสู่ระบบก่อนสั่งซื้อ";
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}
if (empty($cart)) {
  $_SESSION['cart_flash'] = "ตะกร้าสินค้าว่าง";
  header("Location: /~cs6636089/GearZone/backend/cart_view.php");
  exit;
}
if ($addr === '') {
  $_SESSION['cart_flash'] = "กรุณากรอกที่อยู่จัดส่ง";
  header("Location: /~cs6636089/GearZone/backend/checkout.php");
  exit;
}

/* คำนวณยอดรวมจากตะกร้า */
$grand = 0.0;
foreach ($cart as $pid => $it) {
  $grand += ((float)$it['price'] * (int)$it['qty']);
}

/* ส่วนลดเดือนเกิด 12% */
$bday_discount = 0.0;
$stmt = $pdo->prepare("SELECT birthdate FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && !empty($user['birthdate'])) {
  $birth_month   = (int)date('n', strtotime($user['birthdate']));
  $current_month = (int)date('n');
  if ($birth_month === $current_month) {
    $bday_discount = round($grand * 0.12, 2);
  }
}

/* โปรชิ้นที่ 2 ลด 50%  */
$promo2_discount = 0.0;
foreach ($cart as $pid => $it) {
  $q = $pdo->prepare("SELECT stock_quantity FROM Products WHERE product_id = ?");
  $q->execute([$pid]);
  $row = $q->fetch(PDO::FETCH_ASSOC);
  $stock_now = $row ? (int)$row['stock_quantity'] : 0;

  if ($stock_now > 40) {
    $pairs = intdiv((int)$it['qty'], 2);
    $promo2_discount += $pairs * 0.5 * (float)$it['price'];
  }
}

/* ยอดสุทธิที่จะบันทึก */
$final_total = max(0, $grand - $bday_discount - $promo2_discount);

$stmt = $pdo->prepare("INSERT INTO Orders(user_id, payment_status, order_status, shipping_address, total_amount, order_day)
                VALUES (?, 'Pending', 'Pending', ?, ?, CURRENT_DATE)");
$stmt->execute([$user_id, $addr, $final_total]);
$order_id = (int)$pdo->lastInsertId();

/* บันทึกรายการสินค้า + ตัดสต็อก */
$ins = $pdo->prepare("INSERT INTO Order_items(order_id, product_id, unit_price, total_price, quantity)
                      VALUES (?, ?, ?, ?, ?)");
$upd = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

foreach ($cart as $pid => $it) {
  $qty   = (int)$it['qty'];
  $price = (float)$it['price'];
  $sub   = $price * $qty;

  $ins->execute([$order_id, $pid, $price, $sub, $qty]);
  $upd->execute([$qty, $pid]);
}

$_SESSION['cart'] = [];
header("Location: /~cs6636089/GearZone/backend/order_success.php?order_id=" . $order_id);
exit;
