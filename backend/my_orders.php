<?php
session_start();
include "./connect.php";

if (isset($_SESSION['user_id'])) { 
    $user_id = $_SESSION['user_id']; 
} else { 
    $user_id = 0; 
}

if ($user_id == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html"); 
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM Orders WHERE user_id = ? ORDER BY order_id DESC");
$stmt->bindParam(1, $user_id);
$stmt->execute();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>คำสั่งซื้อของฉัน</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap{
        max-width:1100px;
        margin:20px auto 40px
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
    .btn{
        padding:8px 12px;
        border:none;
        border-radius:8px;
        background:red;
        color:#fff;
        text-decoration:none
    }
  </style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <div class="brand">GEARZONE</div>
    <nav class="navlinks">
      <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
      <a href="/~cs6636089/GearZone/backend/my_orders.php" class="active">คำสั่งซื้อของฉัน</a>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
     <?php endif; ?>
    </nav>
  </div>
</header>

<main class="wrap">
  <h3 style="color:black;text-align:center;">คำสั่งซื้อของฉัน</h3>
  <table>
    <thead>
      <tr>
        <th>วันสั่งซื้อ</th>
        <th>ยอดรวม (฿)</th>
        <th>ชำระเงิน</th>
        <th>สถานะ</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php while($o = $stmt->fetch()){ ?>
      <tr>

        <td><?php echo $o['order_day']; ?></td>
        <td><?php echo $o['total_amount']; ?></td>
        <td><?php echo $o['payment_status']; ?></td>
        <td><?php echo $o['order_status']; ?></td>
        <td><a class="btn" href="/~cs6636089/GearZone/backend/order_detail.php?id=<?php echo $o['order_id']; ?>">รายละเอียด</a></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
