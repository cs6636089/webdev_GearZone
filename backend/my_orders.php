<?php
session_start();
include "./connect.php";

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT Orders.order_id, Orders.order_day, Orders.total_amount, 
               Orders.payment_status, Orders.order_status,
               Shipping_tracking.tracking_number, Shipping_tracking.current_status
               FROM Orders LEFT JOIN Shipping_tracking
               ON Shipping_tracking.order_id = Orders.order_id 
               WHERE Orders.user_id = ? ORDER BY Orders.order_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
?>

<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <title>คำสั่งซื้อของฉัน</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap {
      max-width: 1100px;
      margin: 20px auto 40px;
    }

    table {
      width: 100%;
      background: #fff;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
      color: #222;
    }

    th {
      background: #f6f6f6;
    }

    .btn {
      padding: 6px 10px;
      border: none;
      border-radius: 8px;
      background: red;
      color: #fff;
      text-decoration: none;
      cursor: pointer;
    }

    .upload-form {
      margin-top: 6px;
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
    <h3 style="color:black; text-align:center;">ประวัติคำสั่งซื้อของฉัน</h3>

    <table>
      <thead>
        <tr>
          <th>วันที่สั่งซื้อ</th>
          <th>ยอดรวม (฿)</th>
          <th>สถานะชำระเงิน</th>
          <th>สถานะคำสั่งซื้อ</th>
          <th>เลขพัสดุ</th>
          <th>ดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($o = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
          <tr>
            <td><?php echo $o['order_day']; ?></td>
            <td><?php echo $o['total_amount']; ?></td>
            <td><?php echo $o['payment_status']; ?></td>
            <td><?php echo $o['order_status']; ?></td>
            <td>
              <?php
              if (!empty($o['tracking_number'])) {
                echo "<strong>" . $o['tracking_number'] . "</strong><br>";
                echo "<small style='color:#555;'>สถานะขนส่ง: " . $o['current_status'] . "</small>";
              } else {
                echo "-";
              }
              ?>
            </td>
            <td>
              <a class="btn" href="/~cs6636089/GearZone/backend/order_detail.php?id=<?php echo $o['order_id']; ?>">รายละเอียด</a>

              <?php if ($o['payment_status'] == 'Pending') { ?>
                <form class="upload-form" action="/~cs6636089/GearZone/backend/upload_slip.php" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                  <input type="file" name="slip" accept=".jpg,.jpeg,.png,.pdf" required>
                  <button type="submit" class="btn">ส่งสลิป</button>
                </form>
              <?php } elseif ($o['payment_status'] == 'Pending Review') { ?>
                <p style="color:orange; font-size:14px;">รอตรวจสอบสลิป</p>
              <?php } elseif ($o['payment_status'] == 'Paid') { ?>
                <p style="color:green; font-size:14px;">ชำระเงินเรียบร้อย</p>
              <?php } ?>
              <a href="/~cs6636089/GearZone/backend/report_issue.php?order_id=<?php echo $o['order_id']; ?>"
                style="margin-top:6px; display:inline-block; 
                        color:#555; text-decoration:underline; font-size:14px;"
                onmouseover="this.style.color='red'"
                onmouseout="this.style.color='#555'">
                รายงานปัญหา
              </a>


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