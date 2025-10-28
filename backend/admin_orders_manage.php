<?php
// backend/admin_orders_manage.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

/* ---------- Actions: verify payment ---------- */
$act = $_GET['act'] ?? '';
if ($act === 'verify' && isset($_GET['order_id'], $_GET['set'])) {
    $oid = (int)$_GET['order_id'];
    $set = trim($_GET['set']);

    $allow = [
        'Paid'            => ['payment' => 'Paid',            'order' => 'processing'],
        'Failed'          => ['payment' => 'Failed',          'order' => null],
        'Pending Review'  => ['payment' => 'Pending Review',  'order' => null],
        'Pending'         => ['payment' => 'Pending',         'order' => null],
    ];
    if (isset($allow[$set]) && $oid > 0) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE Orders SET payment_status=? WHERE order_id=?")
                ->execute([$allow[$set]['payment'], $oid]);
            if ($allow[$set]['order'] !== null) {
                $pdo->prepare("UPDATE Orders SET order_status=? WHERE order_id=?")
                    ->execute([$allow[$set]['order'], $oid]);
            }
            $pdo->commit();
            header("Location: admin_orders_manage.php?ok=updated");
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            header("Location: admin_orders_manage.php?ok=error");
            exit;
        }
    }
}

/* ---------- Filters ---------- */
$pay  = $_GET['pay']  ?? '';
$stat = $_GET['stat'] ?? '';

$payOpts = $pdo->query("SELECT DISTINCT payment_status FROM Orders ORDER BY payment_status")->fetchAll(PDO::FETCH_COLUMN);
if (!$payOpts) {
    $payOpts = ['Pending Review', 'Pending', 'Paid', 'Failed'];
}

/* ---------- Query: include product names ---------- */
$sql = "SELECT
          o.order_id,
          o.user_id,
          u.username, u.email,
          o.total_amount, o.order_day,
          o.payment_status, o.order_status,
          o.payment_slip,
          st.tracking_number, st.current_status,
          GROUP_CONCAT(DISTINCT p.product_name ORDER BY p.product_name SEPARATOR ', ') AS items
        FROM Orders o
        JOIN Users u ON u.user_id = o.user_id
        LEFT JOIN Shipping_tracking st ON st.order_id = o.order_id
        LEFT JOIN Order_items oi ON oi.order_id = o.order_id
        LEFT JOIN Products p ON p.product_id = oi.product_id
        WHERE 1=1";
$args = [];
if ($pay !== '') {
    $sql .= " AND o.payment_status = ?";
    $args[] = $pay;
}
if ($stat !== '') {
    $sql .= " AND o.order_status   = ?";
    $args[] = $stat;
}

/* ===== ให้รายการ Shipped อยู่ล่างสุด ===== */
$sql .= " GROUP BY o.order_id
          ORDER BY
            CASE WHEN LOWER(COALESCE(st.current_status,'')) = 'shipped' THEN 2 ELSE 0 END ASC,
            o.order_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

/* flash */
$ok = $_GET['ok'] ?? '';
$okMsg = [
    'updated' => 'อัปเดตสถานะแล้ว',
    'error'   => 'เกิดข้อผิดพลาดในการอัปเดต',
][$ok] ?? '';
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin: คำสั่งซื้อ</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        body.admin-page main {
            padding: 32px 16px 70px;
        }

        .admin-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-card {
            background: #fff;
            color: #111;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .06);
            padding: 14px 18px;
            margin-bottom: 20px;
        }

        .admin-card form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }

        .admin-card select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            color: #111;
            font-size: 15px;
        }

        .btn-red,
        .btn-gray,
        .btn-green,
        .btn-dark {
            display: inline-block;
            text-decoration: none;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .05);
        }

        .btn-red {
            background: #ff3b2f;
            color: #fff;
        }

        .btn-red:hover {
            background: #cc2f25;
        }

        .btn-gray {
            background: #ddd;
            color: #111;
        }

        .btn-gray:hover {
            background: #ccc;
        }

        .btn-green {
            background: #2e7d32;
            color: #fff;
        }

        .btn-green:hover {
            background: #246427;
        }

        .btn-dark {
            background: #444;
            color: #fff;
        }

        .btn-dark:hover {
            background: #333;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            color: #111;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .05);
        }

        .admin-table th,
        .admin-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }

        .admin-table th {
            color: #ff3b2f;
            font-weight: 700;
            background: #fafafa;
            font-size: 15px;
        }

        .admin-table td {
            font-size: 15px;
            line-height: 1.4;
        }

        .admin-table tbody tr:hover {
            background: #f9f9f9;
        }

        .admin-table td:nth-child(3) {
            text-align: right;
        }

        .admin-table td:nth-child(2) {
            word-break: break-word;
            max-width: 300px;
        }

        /* Badge การชำระเงินเดิม */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
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

        /* Badge "สถานะจัดส่ง" — ทำ Shipped เป็นสีเขียว */
        .badge.ship {
            background: #2e7d32;
        }

        /* <<<<<< เขียว */
        .badge.ship-pending {
            background: #f7b500;
            color: #222;
        }

        /* ใช้ตอนอยากแยกสี pending */

        .alert {
            background: #e8f8ed;
            color: #0f5132;
            border: 1px solid #badbcc;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert.err {
            background: #fbeaea;
            color: #842029;
            border-color: #f5c2c7;
        }

        .alert .close {
            background: transparent;
            border: none;
            font-size: 15px;
            cursor: pointer;
            color: inherit;
        }

        /* ติ๊กเขียวฝั่ง "จัดการ" เมื่อ Shipped แล้ว */
        .tick {
            display: inline-flex;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #2e7d32;
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            line-height: 1;
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

            <?php if ($okMsg): ?>
                <div class="alert <?= $ok === 'error' ? 'err' : '' ?>" id="flash-ok">
                    <div><?= htmlspecialchars($okMsg) ?></div>
                    <button class="close" onclick="document.getElementById('flash-ok').remove()">×</button>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <form method="get">
                    <select name="pay">
                        <option value="">ชำระเงิน: ทั้งหมด</option>
                        <?php foreach ($payOpts as $p): ?>
                            <option value="<?= htmlspecialchars($p) ?>" <?= $pay === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
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

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ผู้สั่ง</th>
                        <th>สินค้า</th>
                        <th>ยอดสุทธิ</th>
                        <th>วันที่</th>
                        <th>สลิป</th>
                        <th>ชำระเงิน</th>
                        <th>สถานะจัดส่ง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $ps = (string)$r['payment_status'];
                        $isPaid = (stripos($ps, 'paid') === 0);
                        $ship = strtolower((string)$r['current_status']);
                        $isShipped = ($ship === 'shipped');
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($r['username']) ?><br>
                                <span class="small" style="color:#666"><?= htmlspecialchars($r['email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['items'] ?: '-') ?></td>
                            <td>฿<?= number_format($r['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($r['order_day']) ?></td>
                            <td>
                                <?php if (!empty($r['payment_slip'])): ?>
                                    <a class="btn-gray" href="<?= htmlspecialchars($r['payment_slip']) ?>" target="_blank" rel="noopener">ดูสลิป</a>
                                <?php else: ?>
                                    <span class="small" style="color:#999">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- สถานะชำระเงิน (เดิม) -->
                            <td>
                                <?php
                                $payCls = $isPaid ? 'paid' : ((stripos($ps, 'fail') === 0) ? 'failed' : 'pending');
                                ?>
                                <span class="badge <?= $payCls ?>"><?= htmlspecialchars($ps) ?></span>
                            </td>

                            <!-- สถานะจัดส่ง: Shipped = เขียว -->
                            <td>
                                <?php if ($r['tracking_number']): ?>
                                    <span class="badge <?= $isShipped ? 'ship' : 'ship-pending' ?>">
                                        <?= htmlspecialchars($r['current_status']) ?>
                                    </span><br>
                                    <span class="small" style="color:#666">TN: <?= htmlspecialchars($r['tracking_number']) ?></span>
                                <?php else: ?>
                                    <span class="badge pending">no tracking</span>
                                <?php endif; ?>
                            </td>

                            <!-- จัดการ: ถ้า Shipped → ติ๊กอย่างเดียว, ถ้า Paid แล้ว → เหลือปุ่มอัปเดต -->
                            <td>
                                <?php if ($isShipped): ?>
                                    <span class="tick">✓</span>
                                <?php else: ?>
                                    <a class="btn-red" href="admin_orders_update.php?order_id=<?= (int)$r['order_id'] ?>">อัปเดต</a>
                                    <?php if (!$isPaid): ?>
                                        <a class="btn-green"
                                            href="admin_orders_manage.php?act=verify&set=Paid&order_id=<?= (int)$r['order_id'] ?>"
                                            onclick="return confirm('ยืนยันการชำระเงินออเดอร์นี้ ?')">ยืนยันชำระ</a>
                                        <a class="btn-dark"
                                            href="admin_orders_manage.php?act=verify&set=Failed&order_id=<?= (int)$r['order_id'] ?>"
                                            onclick="return confirm('มาร์คไม่ผ่านออเดอร์นี้ ?')">ไม่ผ่าน</a>
                                    <?php endif; ?>
                                <?php endif; ?>
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

    <script>
        setTimeout(() => {
            const el = document.getElementById('flash-ok');
            el && el.remove();
        }, 3000);
    </script>
</body>

</html>