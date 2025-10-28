<?php
// backend/admin_orders_manage.php (แก้ชื่อไฟล์ตามที่คุณใช้อยู่)
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

// ฟิลเตอร์
$pay  = $_GET['pay']  ?? '';
$stat = $_GET['stat'] ?? '';

$sql = "SELECT o.order_id, o.user_id, u.username, u.email,
               o.total_amount, o.order_day, o.payment_status, o.order_status,
               st.tracking_number, st.current_status
        FROM Orders o
        JOIN Users u ON u.user_id = o.user_id
        LEFT JOIN Shipping_tracking st ON st.order_id = o.order_id
        WHERE 1=1";
$args = [];
if ($pay !== '') {
    $sql .= " AND o.payment_status = ?";
    $args[] = $pay;
}
if ($stat !== '') {
    $sql .= " AND o.order_status = ?";
    $args[] = $stat;
}
$sql .= " ORDER BY o.order_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin: คำสั่งซื้อ</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        /* ===== Light admin style เฉพาะหน้านี้ ===== */
        body.admin-page main {
            padding: 32px 16px 80px;
        }

        .admin-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-card {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            padding: 18px;
            margin: 0 0 18px;
        }

        .admin-card form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .admin-card select,
        .admin-card input {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fff;
            color: #111;
        }

        .btn-red {
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
        }

        .btn-red:hover {
            background: #cc2f25;
        }

        a.btn-red {
            text-decoration: none;
            display: inline-block;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            color: #111;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .admin-table th {
            color: #ff3b2f;
            font-weight: 700;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            color: #fff;
        }

        .badge.pending {
            background: #888;
        }

        .badge.paid {
            background: #156a2f;
        }

        .badge.failed {
            background: #7a2222;
        }

        .badge.ship {
            background: #254a7a;
        }
    </style>
</head>

<body class="admin-page">
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks">
                <a href="/~cs6636089/GearZone/frontend/admin.php">แดชบอร์ด</a>
                <a href="admin_products.php">สินค้า</a>
                <a href="admin_orders_manage.php" class="active">คำสั่งซื้อ</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="admin-wrap">
            <div class="section-title" style="text-align:center; margin:0 0 10px;">
                <h3>รายการคำสั่งซื้อทั้งหมด</h3>
            </div>

            <!-- ฟอร์มกรอง -->
            <div class="admin-card">
                <form method="get">
                    <select name="pay">
                        <option value="">ชำระเงิน: ทั้งหมด</option>
                        <?php foreach (['pending', 'paid', 'failed'] as $p): ?>
                            <option value="<?= $p ?>" <?= $pay === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="stat">
                        <option value="">สถานะออเดอร์: ทั้งหมด</option>
                        <?php foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $stat === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-red" type="submit">กรอง</button>
                </form>
            </div>

            <!-- ตาราง -->
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ผู้สั่ง</th>
                        <th>ยอดสุทธิ</th>
                        <th>วันที่</th>
                        <th>ชำระเงิน</th>
                        <th>สถานะจัดส่ง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['order_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($r['username']) ?><br>
                                <span class="small" style="color:#666"><?= htmlspecialchars($r['email']) ?></span>
                            </td>
                            <td>฿<?= number_format($r['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($r['order_day']) ?></td>
                            <td>
                                <span class="badge <?= $r['payment_status'] === 'paid' ? 'paid' : ($r['payment_status'] === 'failed' ? 'failed' : 'pending') ?>">
                                    <?= htmlspecialchars($r['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['tracking_number']): ?>
                                    <span class="badge ship"><?= htmlspecialchars($r['current_status']) ?></span><br>
                                    <span class="small" style="color:#666">TN: <?= htmlspecialchars($r['tracking_number']) ?></span>
                                <?php else: ?>
                                    <span class="badge pending">no tracking</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- แก้ลิงก์ให้ตรงชื่อไฟล์อัปเดต -->
                                <a class="btn-red" href="admin_orders_update.php?order_id=<?= (int)$r['order_id'] ?>">อัปเดต</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">© 2025 GearZone</div>
    </footer>
</body>

</html>