<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html"); 
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Orders WHERE user_id = ? ORDER BY order_id DESC");
$stmt->execute([$user_id]);
?>


<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>คำสั่งซื้อของฉัน - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap { 
        max-width:1100px; 
        margin:20px auto 40px; 
    }
    table { 
        width:100%; 
        background:#fff; 
        border-collapse:collapse; 
        border-radius:12px; 
        overflow:hidden; 
    }
    th, td { 
        padding:12px; 
        border-bottom:1px solid #eee; 
        text-align:left; 
    }
    th { 
        background:#f6f6f6; 
    }
    .btn { 
        padding:6px 10px; 
        border:none; 
        border-radius:8px; 
        background:red; 
        color:#fff; 
        text-decoration:none; 
        cursor:pointer; 
    }
    .upload-form { 
        margin-top:6px; 
    }

    .table-orders, .table-orders th, .table-orders td { 
        color:#222 !important; 
    }
    .table-orders { 
        background:#fff; 
        opacity:1; 
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
  <h3 style="color:black;text-align:center;">ประวัติการสั่งซื้อของฉัน</h3>
  <table class="table-orders">
    <thead>
      <tr>
        <th>วันสั่งซื้อ</th>
        <th>ยอดรวม (฿)</th>
        <th>ชำระเงิน</th>
        <th>สถานะ</th>
        <th>ดำเนินการ</th>
      </tr>
    </thead>
    <tbody>
    <?php while($o = $stmt->fetch()){ ?>
      <tr>
        <td><?php echo $o['order_day']; ?></td>
        <td><?php echo $o['total_amount']; ?></td>
        <td><?php echo $o['payment_status']; ?></td>
        <td><?php echo $o['order_status']; ?></td>
        <td>
          <a class="btn" href="/~cs6636089/GearZone/backend/order_detail.php?id=<?php echo $o['order_id']; ?>">รายละเอียด</a>

          <?php if ($o['payment_status'] == 'Pending') { ?>
            <form class="upload-form" action="/~cs6636089/GearZone/backend/upload_slip.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
              <input type="file" name="slip" accept=".jpg,.jpeg,.png,.pdf" required>
              <button type="submit" class="btn">ส่งสลิป</button>
            </form>
          <?php } elseif ($o['payment_status'] == 'Pending Review') { ?>
            <p style="color:orange; font-size:14px;">รอตรวจสอบสลิป...</p>
          <?php } elseif ($o['payment_status'] == 'Paid') { ?>
            <p style="color:green; font-size:14px;">ชำระเงินเรียบร้อย</p>
          <?php } ?>
        </td>
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
