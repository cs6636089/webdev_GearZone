<?php
session_start();
include "./connect.php";

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT Orders.*, Users.birthdate FROM Orders 
  JOIN Users ON Users.user_id = Orders.user_id WHERE Orders.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT Order_items.*, Products.product_name FROM Order_items
  LEFT JOIN Products ON Products.product_id = Order_items.product_id WHERE Order_items.order_id = ?");
$stmt2->execute([$order_id]);
$items = $stmt2->fetchAll(PDO::FETCH_ASSOC);


/* ส่วนลดเดือนเกิด */
$has_bday = false;
if ($order && !empty($order['birthdate']) && !empty($order['order_day'])) {
  $bd_m  = (int)date('n', strtotime($order['birthdate']));
  $ord_m = (int)date('n', strtotime($order['order_day']));
  $has_bday = ($bd_m === $ord_m);
}

/* คำนวณยอดรวมก่อนส่วนลด */
$subtotal = 0.0;
foreach ($items as $it) {
  $subtotal += (float)$it['total_price'];
}

$bday_discount_expected = $has_bday ? round($subtotal * 0.12, 2) : 0.0;

/* ส่วนลดจริง = ยอดรวมสินค้า - ยอดที่บันทึกใน Orders */
$charged = (float)$order['total_amount'];
$diff = $subtotal - $charged;

/* ตรวจว่ามีโปรชิ้นที่ 2 */
$has_pair50 = false;
if ($has_bday) {
  // ถ้ามีส่วนลดมากกว่าที่คาดจากเดือนเกิด แสดงว่ามีโปรชิ้นที่ 2 ด้วย
  if ($diff > ($bday_discount_expected + 0.01)) $has_pair50 = true;
} else {
  // ไม่มีเดือนเกิดแต่ยอดรวมลดลง แสดงว่ามีโปรชิ้นที่ 2
  if ($diff > 0.01) $has_pair50 = true;
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>รายละเอียดคำสั่งซื้อ - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap {
      max-width: 1100px;
      margin: 20px auto 40px;
    }
    .box {
      background: #fff;
      color: #333;
      padding: 16px;
      border-radius: 12px;
      margin-bottom: 16px;
    }
    table {
      width: 100%;
      background: #fff;
      color: #333;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th {
      background: #f6f6f6;
    }
    .discount-badges {
      margin-top: 6px;
      font-size: .95em;
      color: #d00;
    }
    .discount-badges span {
      display: inline-block;
      margin-right: 10px;
    }
  </style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <div class="brand">GEARZONE</div>
    <nav class="navlinks">
      <a href="/~cs6636089/GearZone/backend/my_orders.php">คำสั่งซื้อของฉัน</a>
    </nav>
  </div>
</header>

<main class="wrap">
<?php if ($order) { ?>
  <div class="box">
    <h3>เลขที่คำสั่งซื้อ: <?php echo htmlspecialchars($order['order_id']); ?></h3>

    <?php
      // แสดงส่วนลดที่ได้รับจริง
      if ($has_bday || $has_pair50) {
        echo '<div class="discount-badges">';
        if ($has_bday)   echo '<span>ได้รับส่วนลดเดือนเกิด 12%</span>';
        if ($has_pair50) echo '<span>ได้รับส่วนลดโปรชิ้นที่ 2 ลด 50%</span>';
        echo '</div>';
      } else {
        echo '<div class="discount-badges" style="color:#666;">(ไม่มีส่วนลดพิเศษ)</div>';
      }
    ?>

    <p>วันสั่งซื้อ: <?php echo htmlspecialchars($order['order_day']); ?></p>
    <p>ยอดรวม: ฿<?php echo number_format($order['total_amount'], 2); ?></p>
    <p>ชำระเงิน: <?php echo htmlspecialchars($order['payment_status']); ?></p>
    <p>สถานะคำสั่งซื้อ/จัดส่ง: <?php echo htmlspecialchars($order['order_status']); ?></p>
    <p>ที่อยู่จัดส่ง: <?php echo htmlspecialchars($order['shipping_address']); ?></p>
  </div>

  <table>
    <thead>
      <tr>
        <th>สินค้า</th>
        <th>ราคา/ชิ้น</th>
        <th>จำนวน</th>
        <th>รวม</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it) { ?>
        <tr>
          <td><?php echo htmlspecialchars($it['product_name']); ?></td>
          <td>฿<?php echo number_format($it['unit_price'], 2); ?></td>
          <td><?php echo (int)$it['quantity']; ?></td>
          <td>฿<?php echo number_format($it['total_price'], 2); ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
<?php } else { ?>
  <div class="box">ไม่พบคำสั่งซื้อ</div>
<?php } ?>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
