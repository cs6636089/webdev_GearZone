<?php
require_once __DIR__ . '/../backend/config/auth.php';  // มี require session ข้างในแล้ว
requireLogin();                                        // ต้องล็อกอินเท่านั้น
$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GearZone - หน้าใช้งานผู้ใช้</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks" aria-label="Top Links">
                <a href="/~cs6636089/GearZone/frontend/user.php" class="active">หน้าของฉัน</a>
                <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="section-title">
            <h3>สวัสดี, <?php echo htmlspecialchars($user['first'] ?: $user['username']); ?> 👋</h3>
            <p>นี่คือหน้าผู้ใช้หลังเข้าสู่ระบบ</p>
        </div>

        <section class="features">
            <div class="feature">
                <h4>ข้อมูลบัญชี</h4>
                <p class="small">
                    อีเมล: <?php echo htmlspecialchars($user['email']); ?><br>
                    เบอร์: <?php echo htmlspecialchars($user['phone'] ?? '-'); ?><br>
                    ที่อยู่: <?php echo htmlspecialchars($user['address'] ?? '-'); ?>
                </p>
            </div>

            <div class="feature">
                <h4>ไปเลือกซื้อสินค้า</h4>
                <p class="small"><a class="btn" href="/~cs6636089/GearZone/index.html">หน้าหลัก</a></p>
            </div>

            <div class="feature">
                <h4>โปรไฟล์</h4>
                <p class="small"><a class="btn" href="/~cs6636089/GearZone/frontend/profile.html">แก้ไขโปรไฟล์</a></p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">&copy; 2025 GearZone</div>
    </footer>
</body>

</html>