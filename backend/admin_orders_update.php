<?php
// backend/admin_orders_update.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

$id = (int)($_GET['order_id'] ?? 0);
if (!$id) exit('missing order_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pay  = $_POST['payment_status'] ?? 'pending';
    $stat = $_POST['order_status']   ?? 'pending';
    $tn   = trim($_POST['tracking_number'] ?? '');
    $ship = trim($_POST['current_status'] ?? '');

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE Orders SET payment_status=?, order_status=? WHERE order_id=?")
            ->execute([$pay, $stat, $id]);

        // update shipping info
        $chk = $pdo->prepare("SELECT tracking_id FROM Shipping_tracking WHERE order_id=?");
        $chk->execute([$id]);
        if ($chk->fetch()) {
            $pdo->prepare("UPDATE Shipping_tracking SET tracking_number=?, current_status=? WHERE order_id=?")
                ->execute([$tn ?: null, $ship ?: null, $id]);
        } else {
            $pdo->prepare("INSERT INTO Shipping_tracking(order_id, tracking_number, current_status) VALUES(?,?,?)")
                ->execute([$id, $tn ?: null, $ship ?: null]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        exit('update error: ' . $e->getMessage());
    }

    header('Location: admin_orders_manage.php?updated=1');
    exit;
}

$sql = "SELECT o.*, u.username, u.email, st.tracking_number, st.current_status
        FROM Orders o
        JOIN Users u ON u.user_id=o.user_id
        LEFT JOIN Shipping_tracking st ON st.order_id=o.order_id
        WHERE o.order_id=?";
$st = $pdo->prepare($sql);
$st->execute([$id]);
$o = $st->fetch();
if (!$o) exit('order not found');
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin: อัปเดตคำสั่งซื้อ #<?= $id ?></title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        /* ===== Light Theme (ขาวเหมือนการ์ดสินค้า) ===== */
        body.admin-page main {
            padding: 32px 16px 80px;
        }

        .admin-wrap {
            max-width: 720px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            padding: 20px;
            margin: 20px 0;
        }

        label {
            display: block;
            margin: 10px 0 4px;
            font-weight: 600;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fff;
            color: #111;
            box-sizing: border-box;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #ff3b2f;
            box-shadow: 0 0 0 3px rgba(255, 60, 48, .2);
        }

        .btn-red {
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-red:hover {
            background: #cc2f25;
        }
    </style>
</head>

<body class="admin-page">
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks">
                <a href="/~cs6636089/GearZone/frontend/admin.php">แดชบอร์ด</a>
                <a href="admin_orders_manage.php" class="active">กลับรายการคำสั่งซื้อ</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="admin-wrap">
            <div class="section-title" style="text-align:center;margin-bottom:20px;">
                <h3>อัปเดตคำสั่งซื้อ #<?= (int)$o['order_id'] ?></h3>
                <p><?= htmlspecialchars($o['username']) ?> — ฿<?= number_format($o['total_amount'], 2) ?></p>
            </div>

            <form method="post" class="card">
                <label>สถานะการชำระเงิน</label>
                <select name="payment_status">
                    <?php foreach (['pending', 'paid', 'failed'] as $v): ?>
                        <option value="<?= $v ?>" <?= $o['payment_status'] === $v ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>

                <label>สถานะคำสั่งซื้อ</label>
                <select name="order_status">
                    <?php foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $v): ?>
                        <option value="<?= $v ?>" <?= $o['order_status'] === $v ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>

                <label>เลขพัสดุ (Tracking Number)</label>
                <input name="tracking_number" value="<?= htmlspecialchars($o['tracking_number'] ?? '') ?>">

                <label>สถานะจัดส่ง</label>
                <input name="current_status" value="<?= htmlspecialchars($o['current_status'] ?? '') ?>" placeholder="e.g., preparing / shipped / delivered">

                <div style="margin-top:18px;text-align:center;">
                    <button class="btn-red">บันทึก</button>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">© 2025 GearZone</div>
    </footer>
</body>

</html>