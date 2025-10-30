<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}
$user_id = $_SESSION['user_id'];


$sql = "SELECT Reports.description, Reports.report_type, Reports.product_id, Reports.order_id,
               Products.product_name, Orders.order_day FROM Reports 
               LEFT JOIN Products ON Products.product_id = Reports.product_id
               LEFT JOIN Orders ON Orders.order_id   = Reports.order_id
               WHERE Reports.user_id = ? ORDER BY Reports.report_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <title>รายงานปัญหาของฉัน - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap {
      max-width: 1100px;
      margin: 20px auto 40px
    }

    table {
      width: 100%;
      background: #fff;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
      color: #222
    }

    th {
      background: #f6f6f6
    }

    .muted {
      color: #666;
      font-size: 14px
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
        <a href="/~cs6636089/GearZone/backend/my_reports.php" class="active">รายงานของฉัน</a>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
        <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <h3 style="color:black;text-align:center;">รายงานปัญหาของฉัน</h3>

    <?php if (empty($rows)) { ?>
      <p class="muted" style="text-align:center;background:#fff;padding:16px;border-radius:12px">
        ยังไม่มีการรายงานปัญหา
      </p>
    <?php } else { ?>
      <table>
        <thead>
          <tr>
            <th>วันที่สั่งซื้อ</th>
            <th>สินค้า</th>
            <th>ประเภทปัญหา</th>
            <th>รายละเอียด</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
            <tr>
              <td><?php echo $r['order_day']; ?></td>
              <td><?php echo $r['product_name'] ?: ('PID ' . $r['product_id']); ?></td>
              <td><?php echo $r['report_type']; ?></td>
              <td><?php echo $r['description']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </main>

  <footer class="footer">
    <div class="footer-left">GEARZONE</div><br>
    <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
  </footer>
</body>

</html>