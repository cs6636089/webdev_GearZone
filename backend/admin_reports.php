<?php
// backend/admin_reports.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

/* ---------- ตัวกรองช่วงเวลา + ค้นหาสินค้า ---------- */
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$q    = trim($_GET['q'] ?? '');

$timeCond = " o.order_day BETWEEN ? AND ? ";
$argsBase = [$from, $to];

/* ---------- Top Products ---------- */
$andProduct = '';
$argsProd = $argsBase;
if ($q !== '') {
    $andProduct = " AND p.product_name LIKE ? ";
    $argsProd[] = "%$q%";
}

$sqlTopProducts = "
  SELECT 
    p.product_name,
    SUM(COALESCE(oi.quantity,0)) AS qty,
    SUM(COALESCE(oi.quantity,0) * COALESCE(oi.unit_price, p.price, 0)) AS sales
  FROM Orders o
  JOIN Order_items oi ON oi.order_id = o.order_id
  JOIN Products p    ON p.product_id = oi.product_id
  WHERE $timeCond
  $andProduct
  GROUP BY p.product_name
  HAVING qty > 0
  ORDER BY sales DESC, qty DESC
  LIMIT 15
";
$st = $pdo->prepare($sqlTopProducts);
$st->execute($argsProd);
$topProducts = $st->fetchAll();

/* ---------- ยอดขายรวมรายวัน (เฉพาะที่ชำระแล้ว) ---------- */
$sqlRevenue = "
  SELECT o.order_day AS d, SUM(o.total_amount) AS total
  FROM Orders o
  WHERE $timeCond AND LOWER(o.payment_status)='paid'
  GROUP BY o.order_day
  ORDER BY o.order_day
";
$st = $pdo->prepare($sqlRevenue);
$st->execute($argsBase);
$byDay = $st->fetchAll();

/* ---------- รายงานปัญหาสินค้า ---------- */
$sqlReports = "
  SELECT r.report_id,
         u.username,
         p.product_name,
         r.report_type,
         r.description,
         o.order_status
  FROM Reports r
  JOIN Users u    ON u.user_id    = r.user_id
  JOIN Products p ON p.product_id = r.product_id
  JOIN Orders o   ON o.order_id   = r.order_id
  WHERE $timeCond
  ORDER BY r.report_id ASC
";
$st = $pdo->prepare($sqlReports);
$st->execute($argsBase);
$reports = $st->fetchAll();

/* ---------- สินค้าที่ราคาแพงกว่าค่าเฉลี่ย ---------- */
$andName = '';
$argsAvg = [];
if ($q !== '') { // ถ้ามีคำค้นสินค้า ใช้กรองชื่อด้วย
    $andName = " AND p.product_name LIKE ? ";
    $argsAvg[] = "%$q%";
}

$sqlAboveAvg = "
SELECT p.product_name, p.price
FROM Products p
WHERE p.price > (SELECT AVG(price) FROM Products)
$andName
ORDER BY p.price DESC, p.product_name
";
$st = $pdo->prepare($sqlAboveAvg);
$st->execute($argsAvg);
$aboveAvg = $st->fetchAll();

/* ---------- สินค้าที่ราคาแพงกว่าค่าเฉลี่ย ---------- */
$andName = '';
$argsAvg = [];
if ($q !== '') { // ถ้ามีคำค้นสินค้า ใช้กรองชื่อด้วย
    $andName = " AND p.product_name LIKE ? ";
    $argsAvg[] = "%$q%";
}

// หาค่าเฉลี่ยราคาสินค้าทั้งหมด
$sqlAvgPrice = "SELECT ROUND(AVG(price), 2) FROM Products WHERE price IS NOT NULL";
$avgPrice = (float)$pdo->query($sqlAvgPrice)->fetchColumn();

$sqlAboveAvg = "
  SELECT p.product_name, p.price
  FROM Products p
  WHERE p.price IS NOT NULL
    AND p.price > (SELECT AVG(price) FROM Products WHERE price IS NOT NULL)
  $andName
  ORDER BY p.price DESC, p.product_name
";
$st = $pdo->prepare($sqlAboveAvg);
$st->execute($argsAvg);
$aboveAvg = $st->fetchAll();

?>

<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>รายงานสรุป - GearZone</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        body {
            color: #222;
        }

        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px 16px 80px;
        }

        h2,
        h3 {
            margin: 0 0 8px;
            font-weight: 800;
            color: #111;
        }

        .sub {
            margin: 0 0 14px;
            color: #555;
            font-weight: 600;
        }

        .filters {
            background: #fff;
            border-radius: 14px;
            padding: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
            margin-bottom: 16px;
        }

        .filters input[type="date"],
        .filters input[type="text"] {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 15px;
        }

        .btn {
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 9px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
        }

        thead th {
            background: #fafafa;
            font-weight: 800;
            color: #111;
            border-bottom: 1px solid #eee;
            padding: 10px;
            text-align: left;
        }

        tbody td {
            border-bottom: 1px solid #f0f0f0;
            padding: 10px;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .section {
            margin: 18px 0 28px;
        }

        hr {
            border-radius: 5px;
        }
    </style>
</head>

<body class="admin-page">
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks">
                <a href="/~cs6636089/GearZone/frontend/admin.php">แดชบอร์ด</a>
                <a href="/~cs6636089/GearZone/backend/admin_products.php">สินค้า</a>
                <a href="/~cs6636089/GearZone/backend/admin_orders_manage.php">คำสั่งซื้อ</a>
                <a href="/~cs6636089/GearZone/backend/admin_reports.php">รายงาน</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="wrap">
            <h2>รายงานสรุป</h2>
            <hr>

            <!-- ยอดขายรวมรายวัน -->
            <div class="section">
                <h3>ยอดขายรวมรายวัน</h3>
                <p class="sub">รวมยอดเฉพาะออเดอร์ที่ชำระแล้ว</p>
                <table>
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>ยอดรวม (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($byDay): foreach ($byDay as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['d']) ?></td>
                                    <td>฿<?= number_format($r['total'], 2) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="2">— ไม่มีข้อมูลในช่วงที่เลือก —</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- สินค้าขายดี -->
            <div class="section">
                <h3>สินค้าขายดี (Top Products)</h3>
                <!-- <p class="sub">เรียงตามยอดขาย (บาท) ในช่วงวันที่เลือก</p> -->
                <table>
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ยอดขาย (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($topProducts): foreach ($topProducts as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                                    <td><?= number_format($r['qty']) ?></td>
                                    <td>฿<?= number_format($r['sales'], 2) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="3">— ไม่มีข้อมูลในช่วงที่เลือก —</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- สินค้าที่ราคาแพงกว่าค่าเฉลี่ย -->
            <div class="section">
                <h3>สินค้าที่ราคาแพงกว่าค่าเฉลี่ย</h3>
                <p class="sub">
                    แสดงสินค้าที่มีราคา > ค่าเฉลี่ยของสินค้าทั้งหมด
                    <br><strong>ราคาเฉลี่ยทั้งหมด: ฿ <?= number_format($avgPrice, 2) ?></strong>
                    <?= $q !== '' ? ' (กรองชื่อด้วย: ' . htmlspecialchars($q) . ')' : '' ?>
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อสินค้า</th>
                            <th>ราคา (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($aboveAvg)): foreach ($aboveAvg as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                                    <td><?= number_format($r['price'], 2) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="2">— ไม่พบสินค้าที่แพงกว่าค่าเฉลี่ย —</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


            <!-- รายงานปัญหาสินค้า -->
            <div class="section">
                <h3>รายงานปัญหาสินค้า (Reports)</h3>
                <!-- <p class="sub">รวมผู้แจ้ง / สินค้า / ประเภทปัญหา / สถานะคำสั่งซื้อ</p> -->
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อลูกค้า</th>
                            <th>สินค้า</th>
                            <th>ประเภท</th>
                            <th>คำอธิบาย</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reports): foreach ($reports as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['username']) ?></td>
                                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                                    <td><?= htmlspecialchars($r['report_type']) ?></td>
                                    <td><?= htmlspecialchars($r['description']) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="6">— ไม่มีรายงานในช่วงที่เลือก —</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">© 2025 GearZone</div>
    </footer>
</body>

</html>