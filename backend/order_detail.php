<?php
session_start();
include "./connect.php";

if (isset($_GET['id'])) { 
    $order_id = $_GET['id']; 
} else { 
    $order_id = 0; 
}

$stmt = $pdo->prepare(" SELECT Orders.*, Users.birthdate
    FROM Orders JOIN Users ON Users.user_id = Orders.user_id
    WHERE Orders.order_id = ?");
$stmt->bindParam(1, $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt2 = $pdo->prepare("SELECT Order_items.*, Products.product_name
                        FROM Order_items
                        LEFT JOIN Products ON Order_items.product_id = Products.product_id
                        WHERE Order_items.order_id = ?");
$stmt2->bindParam(1, $order_id);
$stmt2->execute();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>รายละเอียดคำสั่งซื้อ - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap{
        max-width:1100px;
        margin:20px auto 40px
    }
    .box{
        background:#fff;
        color:#333;
        padding:16px;
        border-radius:12px;
        margin-bottom:16px
    }
    table{
        width:100%;
        background:#fff;
        color:#333;
        border-collapse:collapse;
        border-radius:12px;
        overflow:hidden
    }
    th,td{
        padding:12px;
        border-bottom:1px solid #eee;
        text-align:left
    }
    th{
        background:#f6f6f6
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
      <?php
      $birth_text = '';
      if (!empty($order['birthdate']) && !empty($order['order_day'])) {
          $bd_m  = (int)date('n', strtotime($order['birthdate']));
          $ord_m = (int)date('n', strtotime($order['order_day']));
          if ($bd_m === $ord_m) {
            $birth_text = ' (ได้รับส่วนลดเดือนเกิด 12%)';
          }
      }
      ?>
<h3>
  เลขที่คำสั่งซื้อ: <?php echo $order['order_id']; ?>
  <?php if ($birth_text) { ?>
    <span style="color:#d00; font-size:0.9em;"><?php echo $birth_text; ?></span>
  <?php } ?>
</h3>

      <p>วันสั่งซื้อ: <?php echo $order['order_day']; ?></p>
      <p>ยอดรวม: ฿<?php echo $order['total_amount']; ?></p>
      <p>ชำระเงิน: <?php echo $order['payment_status']; ?></p>
      <p>สถานะคำสั่งซื้อ/จัดส่ง: <?php echo $order['order_status']; ?></p>
      <p>ที่อยู่จัดส่ง: <?php echo $order['shipping_address']; ?></p>
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
        <?php while($it = $stmt2->fetch()){ ?>
          <tr>
            <td><?php echo $it['product_name']; ?></td>
            <td>฿<?php echo $it['unit_price']; ?></td>
            <td><?php echo $it['quantity']; ?></td>
            <td>฿<?php echo $it['total_price']; ?></td>
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
