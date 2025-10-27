<?php
session_start();
include "../backend/connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
  echo "คุณไม่มีสิทธิ์เข้าถึงหน้านี้";
  exit;
}

$stmt = $pdo->query("SELECT * FROM Orders ORDER BY order_id DESC");
$orders = $stmt->fetchAll();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>จัดการคำสั่งซื้อ - Admin</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
</head>
<body>
  <h2 style="text-align:center;">รายการคำสั่งซื้อทั้งหมด</h2>

  <table border="1" cellpadding="8" cellspacing="0" align="center" style="background:#fff;">
    <tr style="background:#eee;">
      <th>เลขที่คำสั่งซื้อ</th>
      <th>ผู้ใช้</th>
      <th>ยอดรวม</th>
      <th>สถานะชำระเงิน</th>
      <th>สลิปโอนเงิน</th>
    </tr>

    <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= $o['order_id'] ?></td>
      <td><?= $o['user_id'] ?></td>
      <td>฿<?= $o['total_amount'] ?></td>
      <td><?= $o['payment_status'] ?></td>
      <td>
        <?php if (!empty($o['payment_slip'])): ?>
          <a href="<?= $o['payment_slip'] ?>" target="_blank">ดูสลิป</a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
