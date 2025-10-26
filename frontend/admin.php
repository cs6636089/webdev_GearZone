<?php
require_once __DIR__ . '/../backend/config/auth.php';
requireAdmin();                                   // ต้องเป็นแอดมินเท่านั้น
$admin = $_SESSION['user'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GearZone - แอดมิน</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks" aria-label="Top Links">
                <a href="/~cs6636089/GearZone/frontend/admin.php" class="active">แดชบอร์ด</a>
                <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="section-title">
            <h3>ยินดีต้อนรับ ผู้ดูแลระบบ: <?php echo htmlspecialchars($admin['username']); ?></h3>
            <p>ศูนย์ควบคุมระบบเบื้องต้น</p>
        </div>

        <section class="features">
            <div class="feature">
                <h4>จัดการสินค้า</h4>
                <p class="small"><a class="btn" href="/~cs6636089/GearZone/backend/admin/products_crud.php">เปิดหน้า</a></p>
            </div>
            <div class="feature">
                <h4>คำสั่งซื้อทั้งหมด</h4>
                <p class="small"><a class="btn" href="/~cs6636089/GearZone/backend/admin/orders_manage.php">เปิดหน้า</a></p>
            </div>
            <div class="feature">
                <h4>รายงานยอดขาย</h4>
                <p class="small"><a class="btn" href="/~cs6636089/GearZone/frontend/reports.html">ดูรายงาน</a></p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">&copy; 2025 GearZone</div>
    </footer>
</body>

</html>