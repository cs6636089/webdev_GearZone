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
        'Paid'            => ['payment' => 'Paid',           'order' => 'prepare'],
        'Failed'          => ['payment' => 'Failed',         'order' => null],
        'Pending Review'  => ['payment' => 'Pending Review', 'order' => null],
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
$ship = $_GET['ship'] ?? '';

$payOpts = ['Pending Review', 'Paid', 'Failed'];
$shipOpts = ['Pending', 'preparing', 'shipped'];

/* ---------- Query ---------- */
/* ---------- Filters ---------- */
$pay  = $_GET['pay']  ?? '';
$ship = $_GET['ship'] ?? '';

$payOpts  = ['Pending Review', 'Paid', 'Failed'];
$shipOpts = ['Pending', 'preparing', 'shipped'];

/* ---------- Query ---------- */
$sql = "SELECT
          o.order_id,
          u.username, u.email,
          o.total_amount, o.order_day,
          o.payment_status, o.order_status, o.payment_slip,
          st.tracking_number, st.current_status,
          GROUP_CONCAT(DISTINCT p.product_name ORDER BY p.product_name SEPARATOR ', ') AS items
        FROM Orders o
        JOIN Users u ON u.user_id = o.user_id
        LEFT JOIN Shipping_tracking st ON st.order_id = o.order_id
        LEFT JOIN Order_items oi ON oi.order_id = o.order_id
        LEFT JOIN Products p ON p.product_id = oi.product_id
        WHERE 1=1";

$args = [];

/* กรองสถานะชำระเงิน */
if ($pay !== '') {
    $sql .= " AND o.payment_status = ?";
    $args[] = $pay;
}

/* กรองสถานะจัดส่ง — ใช้เฉพาะที่มีค่า current_status จริง */
if ($ship !== '') {
    $sql .= " AND st.current_status IS NOT NULL
              AND LOWER(TRIM(st.current_status)) = LOWER(TRIM(?))";
    $args[] = $ship;
}

/* เรียงลำดับ: Pending/preparing ข้างบน, shipped ล่างสุด */
$sql .= " GROUP BY o.order_id
          ORDER BY
            CASE 
              WHEN LOWER(TRIM(COALESCE(st.current_status,'pending'))) = 'shipped' THEN 2
              ELSE 0
            END ASC,
            CASE 
              WHEN LOWER(o.payment_status) = 'paid' THEN 0
              ELSE 1
            END ASC,
            o.order_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

/* Flash message */
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
        /* ===== พื้นหลัง/ระยะขอบหน้า ===== */
        body.admin-page {
            background: #d9d5d5
        }

        body.admin-page main {
            padding: 32px 16px 80px
        }

        .admin-wrap {
            max-width: 1200px;
            margin: 0 auto
        }

        /* ===== หัวข้อใหญ่ สีแดง + เส้นใต้ ===== */
        .section-title {
            margin: 0 0 8px;
            text-align: center
        }

        .section-title h3 {
            display: inline-block;
            margin: 0;
            color: #e7332f;
            font-weight: 900;
            font-size: 44px;
            letter-spacing: .5px
        }

        .section-title h3::after {
            content: "";
            display: block;
            width: 180px;
            height: 6px;
            border-radius: 6px;
            margin: 10px auto 0;
            background: #e7332f;
        }

        /* ===== แถบกรอง (แคปซูลลอยกลาง) ===== */
        .admin-card {
            max-width: 930px;
            margin: 18px auto 14px;
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            padding: 12px 14px
        }

        .admin-card form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            align-items: center
        }

        .admin-card select {
            min-width: 240px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #e3e3e3;
            background: #fff;
            color: #111;
            font-size: 15px
        }

        .btn-red {
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(0, 0, 0, .09)
        }

        .btn-red:hover {
            background: #d62a23
        }

        /* ===== ตาราง ===== */
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            color: #111;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .10)
        }

        .admin-table thead th {
            background: #fafafa;
            color: #ff3b2f;
            font-weight: 800;
            font-size: 15px;
            padding: 12px 12px;
            border-bottom: 1px solid #eee;
            text-align: left
        }

        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 15px
        }

        .admin-table tbody tr:hover {
            background: #f8f8f8
        }

        /* จัดความกว้าง/การจัดวางให้นิ่ง */
        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            text-align: right;
            width: 120px
        }

        .admin-table td:nth-child(2) {
            max-width: 420px;
            word-break: break-word
        }

        /* ===== ปุ่ม/ชิปทรงแคปซูล ===== */
        .btn-gray,
        .btn-green,
        .btn-dark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border: none;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .05)
        }

        .btn-gray {
            background: #e6e6e6;
            color: #111
        }

        .btn-gray:hover {
            background: #d9d9d9
        }

        .btn-green {
            background: #2e7d32;
            color: #fff
        }

        .btn-green:hover {
            background: #256429
        }

        .btn-dark {
            background: #444;
            color: #fff
        }

        .btn-dark:hover {
            background: #333
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            color: #fff
        }

        .badge.paid {
            background: #1a7c35
        }

        .badge.pending {
            background: #8f8f8f
        }

        .badge.failed {
            background: #7a2222
        }

        .badge.ship {
            background: #2e7d32
        }

        .badge.ship-pending {
            background: #f0b400;
            color: #222
        }

        /* tracking ตัวเทาเล็กใต้ชิป */
        .admin-table td .small {
            display: inline-block;
            margin-top: 4px
        }

        /* ติ๊กเขียวเมื่อ Shipped */
        .tick {
            display: inline-flex;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #2e7d32;
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 14px
        }

        /* แจ้งเตือนด้านบน */
        .alert {
            background: #e8f8ed;
            color: #0f5132;
            border: 1px solid #badbcc;
            border-radius: 10px;
            padding: 10px 14px;
            margin: 10px auto 16px;
            max-width: 930px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .alert.err {
            background: #fbeaea;
            color: #842029;
            border-color: #f5c2c7
        }

        .alert .close {
            background: transparent;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: inherit
        }

        /* ปุ่มในคอลัมน์จัดการให้อยู่บรรทัดเดียวกัน */
        .admin-table td:last-child {
            white-space: nowrap;
            /* ห้ามตัดบรรทัด */
        }

        .admin-table td:last-child a,
        .admin-table td:last-child .btn-red,
        .admin-table td:last-child .btn-green,
        .admin-table td:last-child .btn-dark {
            display: inline-block;
            /* เรียงแนวนอน */
            vertical-align: middle;
            /* จัดแนวกลางแนวตั้ง */
            margin-right: 6px;
            /* เว้นระยะห่างระหว่างปุ่ม */
        }

        /* ✅ ปุ่ม "ดูสลิป" ให้อยู่บรรทัดเดียวกัน */
        .admin-table td a.btn-gray {
            display: inline-block;
            /* บังคับให้อยู่แนวนอน */
            vertical-align: middle;
            /* จัดให้อยู่ระดับเดียวกับ badge */
            white-space: nowrap;
            /* ไม่ตัดบรรทัด */
            margin-right: 4px;
            /* เผื่อระยะเล็กน้อยถ้ามี badge ต่อท้าย */
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
                <a href="/~cs6636089/GearZone/backend/admin_reports.php">รายงาน</a>
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

                    <select name="ship">
                        <option value="">สถานะจัดส่ง: ทั้งหมด</option>
                        <?php foreach ($shipOpts as $s): ?>
                            <option value="<?= $s ?>" <?= $ship === $s ? 'selected' : '' ?>><?= $s ?></option>
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
                    <?php foreach ($rows as $r):
                        $ps = (string)$r['payment_status'];
                        $isPaid = (stripos($ps, 'paid') === 0);
                        $ship = strtolower((string)$r['current_status']);
                        $isShipped = ($ship === 'shipped');
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($r['username']) ?><br><span class="small" style="color:#666"><?= htmlspecialchars($r['email']) ?></span></td>
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
                            <td>
                                <?php
                                $payCls = $isPaid ? 'paid' : ((stripos($ps, 'fail') === 0) ? 'failed' : 'pending');
                                ?>
                                <span class="badge <?= $payCls ?>"><?= htmlspecialchars($ps) ?></span>
                            </td>
                            <td>
                                <?php if ($r['tracking_number']): ?>
                                    <span class="badge <?= $isShipped ? 'ship' : 'ship-pending' ?>"><?= htmlspecialchars($r['current_status']) ?></span><br>
                                    <span class="small" style="color:#666">TN: <?= htmlspecialchars($r['tracking_number']) ?></span>
                                <?php else: ?>
                                    <span class="badge pending">no tracking</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isShipped): ?>
                                    <span class="tick">✓</span>
                                <?php else: ?>
                                    <a class="btn-red" href="admin_orders_update.php?order_id=<?= (int)$r['order_id'] ?>">อัปเดต</a>
                                    <?php if (!$isPaid): ?>
                                        <a class="btn-green" href="admin_orders_manage.php?act=verify&set=Paid&order_id=<?= (int)$r['order_id'] ?>" onclick="return confirm('ยืนยันการชำระเงินออเดอร์นี้ ?')">ยืนยันชำระ</a>
                                        <a class="btn-dark" href="admin_orders_manage.php?act=verify&set=Failed&order_id=<?= (int)$r['order_id'] ?>" onclick="return confirm('มาร์คไม่ผ่านออเดอร์นี้ ?')">ไม่ผ่าน</a>
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