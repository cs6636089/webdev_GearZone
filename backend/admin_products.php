<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/auth.php';
requireAdmin();
require_once __DIR__ . '/connect.php';

// --- Actions ---
$act = $_POST['act'] ?? $_GET['act'] ?? '';
if ($act === 'create') {
    $stmt = $pdo->prepare("INSERT INTO Products(product_name, price, category_id, stock_quantity) VALUES(?,?,?,?)");
    $stmt->execute([trim($_POST['product_name']), (float)$_POST['price'], (int)$_POST['category_id'], (int)$_POST['stock_quantity']]);
    header('Location: admin_products.php?ok=created');
    exit;
}
if ($act === 'update') {
    $stmt = $pdo->prepare("UPDATE Products SET product_name=?, price=?, category_id=?, stock_quantity=? WHERE product_id=?");
    $stmt->execute([trim($_POST['product_name']), (float)$_POST['price'], (int)$_POST['category_id'], (int)$_POST['stock_quantity'], (int)$_POST['product_id']]);
    header('Location: admin_products.php?ok=updated');
    exit;
}
if ($act === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id=?");
    $stmt->execute([(int)$_GET['id']]);
    header('Location: admin_products.php?ok=deleted');
    exit;
}

// --- Data ---
$cats = $pdo->query("SELECT category_id, category_name FROM Categories ORDER BY category_name")->fetchAll();
$rows = $pdo->query("SELECT p.*, c.category_name FROM Products p LEFT JOIN Categories c ON c.category_id=p.category_id ORDER BY p.product_id")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM Products WHERE product_id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

// flash status
$ok = $_GET['ok'] ?? '';
$okMsg = [
    'created' => 'เพิ่มสินค้าเรียบร้อยแล้ว',
    'updated' => 'บันทึกการแก้ไขเรียบร้อยแล้ว',
    'deleted' => 'ลบสินค้าเรียบร้อยแล้ว',
][$ok] ?? '';
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin: สินค้า</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        /* ===== Light admin style ===== */
        body.admin-page main {
            padding: 32px 16px 80px;
        }

        .admin-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            padding: 18px;
            margin: 0 0 18px;
        }

        .card h4 {
            margin: 0 0 10px;
            color: #ff3b2f;
        }

        form.inline>* {
            margin: 4px;
        }

        input,
        select {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fff;
            color: #111;
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
            padding: 8px 14px;
            cursor: pointer;
        }

        .btn-red:hover {
            background: #cc2f25;
        }

        .btn-gray {
            background: #ddd;
            color: #111;
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
            cursor: pointer;
        }

        .btn-gray:hover {
            background: #ccc;
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

        /* alert */
        .alert {
            background: #e8f8ed;
            color: #0f5132;
            border: 1px solid #badbcc;
            border-radius: 12px;
            padding: 12px 14px;
            margin: 0 0 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .06);
        }

        .alert .close {
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #0f5132;
        }
    </style>
</head>

<body class="admin-page">
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks">
                <a href="/~cs6636089/GearZone/frontend/admin.php">แดชบอร์ด</a>
                <a href="admin_products.php" class="active">สินค้า</a>
                <a href="admin_orders_manage.php">คำสั่งซื้อ</a>
                <a href="/~cs6636089/GearZone/backend/admin_reports.php">รายงาน</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="admin-wrap">
            <div class="section-title" style="text-align:center; margin:0 0 12px;">
                <h3>จัดการสินค้า</h3>
                <p>เพิ่ม / แก้ไข / ลบ</p>
            </div>

            <?php if ($okMsg): ?>
                <div class="alert" id="flash-ok">
                    <div><?= htmlspecialchars($okMsg) ?></div>
                    <button class="close" onclick="document.getElementById('flash-ok').remove()">×</button>
                </div>
            <?php endif; ?>

            <div class="card">
                <h4><?= $edit ? 'แก้ไขสินค้า #' . $edit['product_id'] : 'เพิ่มสินค้าใหม่' ?></h4>
                <form method="post" class="inline">
                    <input type="hidden" name="act" value="<?= $edit ? 'update' : 'create' ?>">
                    <?php if ($edit): ?>
                        <input type="hidden" name="product_id" value="<?= (int)$edit['product_id'] ?>">
                    <?php endif; ?>

                    <input name="product_name" placeholder="ชื่อสินค้า" required value="<?= htmlspecialchars($edit['product_name'] ?? '') ?>">
                    <input type="number" step="0.01" name="price" placeholder="ราคา" required value="<?= htmlspecialchars($edit['price'] ?? '') ?>">
                    <select name="category_id" required>
                        <option value="">--หมวดหมู่--</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= (int)$c['category_id'] ?>" <?= isset($edit['category_id']) && $edit['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="stock_quantity" min="0" placeholder="สต๊อก" required value="<?= htmlspecialchars($edit['stock_quantity'] ?? '0') ?>">
                    <button class="btn-red"><?= $edit ? 'บันทึกการแก้ไข' : 'เพิ่มสินค้า' ?></button>
                    <?php if ($edit): ?>
                        <a class="btn-gray" href="admin_products.php">ยกเลิก</a>
                    <?php endif; ?>
                </form>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>ราคา</th>
                        <th>สต๊อก</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['product_id'] ?></td>
                            <td><?= htmlspecialchars($r['product_name']) ?></td>
                            <td><?= htmlspecialchars($r['category_name'] ?? '-') ?></td>
                            <td>฿<?= number_format($r['price'], 2) ?></td>
                            <td><?= (int)$r['stock_quantity'] ?></td>
                            <td>
                                <a class="btn-red" href="admin_products.php?edit=<?= (int)$r['product_id'] ?>">แก้ไข</a>
                                <a class="btn-gray" onclick="return confirm('ลบสินค้านี้?')" href="admin_products.php?act=delete&id=<?= (int)$r['product_id'] ?>">ลบ</a>
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