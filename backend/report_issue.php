<?php
session_start();
include "./connect.php";

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


$it = $pdo->prepare("SELECT Order_items.product_id, Products.product_name, Order_items.quantity
                     FROM Order_items LEFT JOIN Products ON Products.product_id = Order_items.product_id
                     WHERE Order_items.order_id = ?");
$it->execute([$order_id]);
$items = $it->fetchAll();
?>


<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>รายงานปัญหา - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap{max-width:800px;margin:20px auto 40px;background:#fff;color:#333;padding:20px;border-radius:12px}
    label{display:block;margin:10px 0 6px}
    select,textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
    textarea{min-height:120px}
    .btn{background:red;color:#fff;border:none;border-radius:8px;padding:10px 14px;cursor:pointer;text-decoration:none}
  </style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <div class="brand">GEARZONE</div>
    <nav class="navlinks">
      <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
      <a href="/~cs6636089/GearZone/backend/my_orders.php">คำสั่งซื้อของฉัน</a>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) { ?>
        <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <?php } else { ?>
        <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
      <?php } ?>
    </nav>
  </div>
</header>

<main class="wrap">
  <h3 style="margin-top:0;">รายงานปัญหาออเดอร์</h3>

  <form action="/~cs6636089/GearZone/backend/submit_report.php" method="post">
    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

    <label>สินค้าในออเดอร์นี้</label>
    <select name="product_id" required>
      <option value="">— เลือกสินค้า —</option>
      <?php foreach ($items as $r) { ?>
        <option value="<?php echo $r['product_id']; ?>">
          <?php echo $r['product_name']; ?> × <?php echo $r['quantity']; ?>
        </option>
      <?php } ?>
    </select>

    <label>ประเภทปัญหา</label>
    <select name="report_type" required>
      <option value="Product Issue">Product Issue</option>
      <option value="Shipping Issue">Shipping Issue</option>
      <option value="Other">Other</option>
    </select>

    <label>รายละเอียดปัญหา</label>
    <textarea name="description" placeholder="อธิบายปัญหา เช่น เสียหาย ตำหนิ ส่งช้า ฯลฯ" required></textarea>

    <div style="margin-top:12px;">
      <button type="submit" class="btn">ส่งรายงาน</button>
      <a class="btn" href="/~cs6636089/GearZone/backend/my_orders.php" style="background:#666;margin-left:6px;">ยกเลิก</a>
    </div>
  </form>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
