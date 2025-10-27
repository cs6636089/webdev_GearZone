<?php
session_start();
include "../backend/connect.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

if (isset($_GET['order_id'])) {
  $order_id = $_GET['order_id']; 
} elseif (isset($_GET['id'])) {
  $order_id = $_GET['id'];
} else {
  $order_id = 0;
}

if ($order_id <= 0) {
  echo "<h3 style='color:red; text-align:center;'>ไม่พบคำสั่งซื้อ</h3>";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
  echo "<h3 style='color:red; text-align:center;'>ไม่พบคำสั่งซื้อ</h3>";
  exit;
}


$itemStmt = $pdo->prepare(" SELECT Order_items.*, Products.product_name
  FROM Order_items LEFT JOIN Products ON Products.product_id = Order_items.product_id
  WHERE Order_items.order_id = ?");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();
?>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>สั่งซื้อสำเร็จ</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .box {
      max-width:800px;
      margin:30px auto;
      background:#fff;
      color:#333;
      padding:20px;
      border-radius:12px;
      text-align:center;
    }
  </style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <div class="brand">GEARZONE</div>
    <nav class="navlinks">
      <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
      <a href="/~cs6636089/GearZone/backend/my_orders.php">คำสั่งซื้อของฉัน</a>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
        <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <?php else: ?>
        <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="box">
  <?php if ($order) { ?>
    <h3>สั่งซื้อเรียบร้อย</h3>
    <p>เลขที่คำสั่งซื้อ: <?php echo $order['order_id']; ?></p>
    <p>สถานะชำระเงิน: <?php echo $order['payment_status']; ?></p>
    <p>สถานะคำสั่งซื้อ: <?php echo $order['order_status']; ?></p>
    <p>ยอดรวม: ฿<?php echo $order['total_amount']; ?></p>


    <?php if (!empty($items)) { ?>
      <div style="text-align:left; margin:16px 0 0;">
        <strong>รายการสินค้า</strong>
        <ul>
          <?php foreach ($items as $it) { ?>
            <li>
              <?php echo $it['product_name']; ?> × <?php echo $it['quantity']; ?>
              — ฿<?php echo $it['total_price']; ?>
            </li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

    <a class="btn btn-red" href="/~cs6636089/GearZone/backend/my_orders.php">ดูคำสั่งซื้อของฉัน</a>
  <?php } else { ?>
    <h3>ไม่พบคำสั่งซื้อ</h3>
  <?php } ?>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
