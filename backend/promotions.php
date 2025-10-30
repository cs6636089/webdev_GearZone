<?php
session_start();
include "../backend/connect.php";

$stmt = $pdo->prepare("SELECT * FROM Products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <title>โปรโมชั่นพิเศษ - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      max-width: 1000px;
      margin: 30px auto;
    }

    .promo-card {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 12px;
      text-align: center;
    }

    .promo-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 6px;
    }

    .badge {
      display: inline-block;
      background: red;
      color: #fff;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 12px;
      margin-top: 6px;
    }
  </style>
</head>

<body>

  <header class="header">
    <div class="container navbar">
      <div class="brand">GEARZONE</div>
      <nav class="navlinks">
        <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
        <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่สินค้า</a>
        <a href="#" class="active">โปรโมชั่น</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
        <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <h2 style="text-align:center; color:black;">โปรโมชั่นสินค้าค้างสต็อกชิ้นที่ 2 ลด 50%</h2>
    <div class="product-grid">
      <?php foreach ($products as $p): ?>
        <?php if ($p['stock_quantity'] > 40): ?> <!-- ค้างสต็อกเกิน 40 ชิ้น -->
          <div class="promo-card">
            <img src="/~cs6636089/GearZone/assets/products/<?= $p['product_id'] ?>.jpg">
            <div style="color:black; font-weight:bold;"><?= htmlspecialchars($p['product_name']) ?></div>
            <div style="color:red;">฿<?= $p['price'] ?></div>
            <div class="badge">โปรชิ้นที่ 2 ลด 50%</div>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
              <form action="/~cs6636089/GearZone/backend/cart.php" method="post" style="margin-top:8px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                <input type="hidden" name="qty" value="1">
                <button type="submit" style="background:red; color:white; border:none; padding:8px 10px; border-radius:6px; cursor:pointer;">
                  เพิ่มลงตะกร้า
                </button>
              </form>
            <?php else: ?>
              <a href="/~cs6636089/GearZone/frontend/login.html" style="color:blue; font-size:14px; text-decoration:underline;">
                เข้าสู่ระบบเพื่อสั่งซื้อ
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-left">GEARZONE</div>
    <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
  </footer>

</body>

</html>