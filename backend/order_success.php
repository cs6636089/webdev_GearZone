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
  <title>สั่งซื้อสำเร็จ - GearZone</title>
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

    <?php 
    if ($order && strtolower($order['payment_status']) !== 'paid') { ?>
        <div style="max-width:800px;margin:16px auto;background:#fff;color:#333;padding:16px;border-radius:12px;text-align:left">
            <h4 style="margin:0 0 10px;color:#d00;">ชำระเงิน / อัปโหลดสลิป</h4>

            <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
                <div style="flex:0 0 240px;text-align:center">
                    <img src="/~cs6636089/GearZone/assets/qr_payment.jpg" alt="QR สำหรับชำระเงิน"
                        style="width:240px;height:240px;object-fit:contain;border:1px solid #eee;border-radius:10px;background:#fafafa">
                    <div style="font-size:13px;color:#666;margin-top:6px">
                        สแกน QR เพื่อชำระยอด: ฿<?php echo $order['total_amount']; ?>
                    </div>
                </div>

                <form action="/~cs6636089/GearZone/backend/upload_slip.php"
                        method="post" enctype="multipart/form-data"
                        style="flex:1;min-width:260px;background:#f9f9f9;border:1px solid #eee;padding:12px;border-radius:10px">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <label style="display:block;margin:6px 0 8px">อัปโหลดสลิปโอนเงิน (รองรับ JPG, PNG, PDF, ≤ 5MB)</label>
                    <input type="file" name="slip" accept="image/*,application/pdf" required
                        style="display:block;margin-bottom:10px">
                    <button type="submit"
                            style="background:red;color:#fff;border:none;border-radius:8px;padding:10px 14px;cursor:pointer">
                    ส่งสลิปให้ตรวจสอบ
                    </button>
                    <div style="font-size:12px;color:#888;margin-top:8px">
                        หลังอัปโหลดแล้ว สถานะจะเปลี่ยนเป็น “รอตรวจสอบ” และแอดมินจะตรวจสอบให้ค่ะ
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>




</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
