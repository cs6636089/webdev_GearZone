<?php
// frontend/admin.php
require_once __DIR__ . '/../backend/config/auth.php'; // ภายในมี session_start() แล้ว
requireAdmin();
require_once __DIR__ . '/../backend/connect.php';

// ===== Summary (ตัวเลขบนแดชบอร์ด) =====
$prod_total = (int)$pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
$low_stock  = (int)$pdo->query("SELECT COUNT(*) FROM Products WHERE stock_quantity <= 5")->fetchColumn();

// ========== คำสั่งซื้อ ==========
$orders_total = (int)$pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

// กำลังดำเนินการ (อิงจาก payment_status)
$processing = (int)$pdo->query("
  SELECT COUNT(*) 
  FROM Orders 
  WHERE LOWER(payment_status) = 'paid'
")->fetchColumn();

// ชำระเงินแล้ว
$paid_count = (int)$pdo->query("
  SELECT COUNT(*) 
  FROM Orders 
  WHERE LOWER(payment_status) = 'paid'
")->fetchColumn();

// รอตรวจ/ยังไม่ชำระ
$pending_pay = (int)$pdo->query("
  SELECT COUNT(*) 
  FROM Orders 
  WHERE LOWER(payment_status) = 'pending review'
")->fetchColumn();

// ========== สถานะจัดส่ง (อิง Shipping_tracking) ==========
$shipped = (int)$pdo->query("
  SELECT COUNT(DISTINCT order_id)
  FROM Shipping_tracking
  WHERE LOWER(current_status) = 'shipped'
")->fetchColumn();

$preparing = (int)$pdo->query("
  SELECT COUNT(DISTINCT order_id)
  FROM Shipping_tracking
  WHERE LOWER(current_status) = 'preparing'
")->fetchColumn();

$pending_ship = (int)$pdo->query("
  SELECT COUNT(DISTINCT order_id)
  FROM Shipping_tracking
  WHERE LOWER(current_status) = 'pending'
     OR current_status IS NULL
     OR TRIM(current_status) = ''
")->fetchColumn();

// รวมสถานะจัดส่งทั้งหมด
$ship_total = $shipped + $preparing + $pending_ship;

?>

<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - GearZone</title>
    <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
    <style>
        /* สไตล์เฉพาะหน้านี้เท่านั้น ไม่กระทบไฟล์อื่น */
        body.admin-page main {
            padding: 32px 16px 80px;
        }

        .dash-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin: 8px 0 24px;
        }

        .kpi {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            padding: 16px 18px;
        }

        .kpi h5 {
            margin: 0 0 6px;
            color: #666;
            font-weight: 700;
        }

        .kpi .num {
            font-size: 28px;
            font-weight: 800;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: #fff;
            color: #111;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            padding: 20px;
            text-align: center;
        }

        .card h4 {
            margin: 8px 0 6px;
        }

        .card p {
            color: #666;
            margin: 0 0 12px;
        }

        .btn {
            display: inline-block;
            background: #ff3b2f;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #cc2f25;
        }

        @media (max-width:768px) {
            .cards {
                gap: 14px;
            }
        }
    </style>
</head>

<body class="admin-page">
    <!-- Header เดิมของโปรเจ็กต์ -->
    <header class="header">
        <div class="container navbar">
            <div class="brand">GEARZONE</div>
            <nav class="navlinks">
                <a href="/~cs6636089/GearZone/frontend/admin.php" class="active">แดชบอร์ด</a>
                <a href="/~cs6636089/GearZone/backend/admin_products.php">สินค้า</a>
                <a href="/~cs6636089/GearZone/backend/admin_orders_manage.php">คำสั่งซื้อ</a>
                <a href="/~cs6636089/GearZone/backend/admin_reports.php">รายงาน</a>
                <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="dash-wrap">
            <div class="section-title" style="text-align:center; margin: 0 0 12px;">
                <h3>แผงควบคุมผู้ดูแลระบบ</h3>
                <p>จัดการสินค้า คำสั่งซื้อ และสถานะการชำระเงิน</p>
            </div>

            <!-- KPI แถวบน -->
            <section class="kpis">
                <div class="kpi">
                    <h5>สินค้าทั้งหมด</h5>
                    <div class="num"><?= number_format($prod_total) ?></div>
                    <div style="color:#999;font-size:15px">คงเหลือต่ำ (&le;5): <?= number_format($low_stock) ?></div>
                </div>
                <div class="kpi">
                    <h5>คำสั่งซื้อทั้งหมด</h5>
                    <div class="num"><?= number_format($orders_total) ?></div>
                    <div style="color:#999;font-size:15px">กำลังดำเนินการ: <?= number_format($processing) ?></div>
                </div>

                <div class="kpi">
                    <h5>ชำระเงินแล้ว</h5>
                    <div class="num"><?= number_format($paid_count) ?></div>
                    <div style="color:#999;font-size:15px">รอตรวจ/ยังไม่ชำระ: <?= number_format($pending_pay) ?></div>
                </div>

                <div class="kpi">
                    <h5>สถานะจัดส่ง</h5>
                    <div class="num"><?= number_format($ship_total) ?></div>
                    <div style="color:#999;font-size:15px">
                        Shipped: <?= number_format($shipped) ?> · preparing: <?= number_format($preparing) ?> · pending: <?= number_format($pending_ship) ?>
                    </div>
                </div>


            </section>

            <!-- การ์ดลัดไปหน้าจัดการหลัก -->
            <section class="cards">
                <div class="card">
                    <h4>จัดการสินค้า</h4>
                    <p>เพิ่ม/แก้ไข/ลบ และอัปเดตสต๊อก</p>
                    <a class="btn" href="/~cs6636089/GearZone/backend/admin_products.php">เปิดหน้า</a>
                </div>
                <div class="card">
                    <h4>คำสั่งซื้อทั้งหมด</h4>
                    <p>ตรวจรายการ ปรับสถานะ จัดการขนส่ง</p>
                    <a class="btn" href="/~cs6636089/GearZone/backend/admin_orders_manage.php">เปิดหน้า</a>
                </div>
                <div class="card">
                    <h4>การชำระเงิน</h4>
                    <p>ดูออเดอร์ที่ชำระแล้ว</p>
                    <a class="btn" href="/~cs6636089/GearZone/backend/admin_orders_manage.php?pay=paid">ดูรายการชำระแล้ว</a>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-left">GEARZONE</div>
        <div class="footer-right">&copy; 2025 GearZone</div>
    </footer>
</body>

</html>