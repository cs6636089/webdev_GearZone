<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

$id = (int)($_GET['order_id'] ?? 0);
if (!$id) exit('missing order_id');

/* โหลดข้อมูลเดิม */
$sql = "SELECT o.*, u.username, u.email, st.tracking_number, st.current_status
        FROM Orders o
        JOIN Users u ON u.user_id=o.user_id
        LEFT JOIN Shipping_tracking st ON st.order_id=o.order_id
        WHERE o.order_id=?";
$st = $pdo->prepare($sql);
$st->execute([$id]);
$o = $st->fetch();
if (!$o) exit('order not found');

$currentPay = (string)$o['payment_status'];
$isPaidLocked = (strcasecmp($currentPay, 'Paid') === 0); // ล็อกถ้าเป็น Paid แล้ว

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pay  = $_POST['payment_status'] ?? $currentPay;
    $tn   = trim($_POST['tracking_number'] ?? '');
    $ship = trim($_POST['current_status'] ?? '');

    // ถ้าจ่ายแล้ว ล็อกสถานะชำระเงินไว้ที่ Paid
    if ($isPaidLocked) {
        $pay = 'Paid';
    }

    // เตรียมค่าที่จะเขียนลง DB (อย่าใส่ NULL ในคอลัมน์ NOT NULL)
    $tnForDb   = ($tn === '') ? '' : $tn;
    $shipForDb = ($ship === '') ? 'Pending' : $ship;

    $pdo->beginTransaction();
    try {
        // อัปเดตสถานะชำระเงิน
        $pdo->prepare("UPDATE Orders SET payment_status=? WHERE order_id=?")
            ->execute([$pay, $id]);

        // มีแถวใน Shipping_tracking แล้วหรือยัง?
        $chk = $pdo->prepare("SELECT tracking_id FROM Shipping_tracking WHERE order_id=?");
        $chk->execute([$id]);                     // ← สำคัญ: ต้อง execute ก่อน fetch
        $exists = (bool)$chk->fetchColumn();

        if ($exists) {
            // อัปเดตแถวเดิม — ถ้าผู้ใช้ไม่ได้กรอกเลขพัสดุใหม่ ให้คงค่าเดิมไว้
            $pdo->prepare("
                UPDATE Shipping_tracking
                SET tracking_number = CASE WHEN ? = '' THEN tracking_number ELSE ? END,
                    current_status  = ?
                WHERE order_id = ?
            ")->execute([$tnForDb, $tnForDb, $shipForDb, $id]);
        } else {
            // ยังไม่มีแถว → สร้างใหม่ (tracking_number เป็นค่าว่างได้ แต่ต้องไม่ใช่ NULL)
            $pdo->prepare("
                INSERT INTO Shipping_tracking(order_id, tracking_number, current_status)
                VALUES (?,?,?)
            ")->execute([$id, $tnForDb, $shipForDb]);
        }

        $pdo->commit();
        header('Location: admin_orders_manage.php?ok=updated');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        exit('update error: ' . $e->getMessage());
    }
}

?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin: อัปเดตคำสั่งซื้อ #<?= (int)$o['order_id'] ?></title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        body {
            background: #d6d3d3;
            margin: 0;
            font-family: 'Rajdhani', system-ui, sans-serif;
        }

        main {
            display: flex;
            align-items: flex-start;
            /* ให้ขึ้นสูงหน่อย */
            justify-content: center;
            min-height: 100vh;
            padding-top: 60px;
            /* ขยับขึ้นมาหน่อย */
        }

        .update-wrap {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .1);
            padding: 28px 32px;
            width: 420px;
            text-align: left;
        }

        .update-wrap h3 {
            text-align: center;
            color: #ff3b2f;
            margin-bottom: 4px;
        }

        .update-wrap p {
            text-align: center;
            color: #444;
            margin-top: 0;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 4px;
        }

        input,
        select {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
            width: 100%;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #ff3b2f;
            box-shadow: 0 0 0 2px rgba(255, 59, 47, .2);
        }

        .btn {
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
            display: block;
            margin: 18px auto 0;
            font-size: 16px;
        }

        .btn:hover {
            background: #cc2f25;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .badge.paid {
            background: #2e7d32;
        }

        .badge.failed {
            background: #7a2222;
        }
    </style>
</head>

<body>
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
        <div class="update-wrap">
            <h3>อัปเดตคำสั่งซื้อ #<?= (int)$o['order_id'] ?></h3>
            <p><?= htmlspecialchars($o['username']) ?> – ฿<?= number_format($o['total_amount'], 2) ?></p>

            <form method="post">

                <!-- สถานะการชำระเงิน -->
                <label>สถานะการชำระเงิน</label>
                <?php if ($isPaidLocked): ?>
                    <span class="badge paid">Paid</span>
                    <input type="hidden" name="payment_status" value="Paid">
                <?php else: ?>
                    <select name="payment_status">
                        <?php foreach (['Pending Review', 'Paid', 'Failed'] as $v): ?>
                            <option value="<?= $v ?>" <?= ($o['payment_status'] === $v ? 'selected' : '') ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <!-- ลบสถานะคำสั่งซื้อออก -->

                <label>เลขพัสดุ (Tracking Number)</label>
                <input name="tracking_number" value="<?= htmlspecialchars($o['tracking_number'] ?? '') ?>">

                <label>สถานะจัดส่ง</label>
                <select name="current_status">
                    <?php foreach (['Pending', 'preparing', 'Shipped'] as $v): ?>
                        <option value="<?= $v ?>" <?= (($o['current_status'] ?? '') === $v ? 'selected' : '') ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>

                <button class="btn">บันทึก</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">© 2025 GearZone</div>
    </footer>
</body>

</html>