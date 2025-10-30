<?php
session_start();
include "./connect.php";

$cat_id = isset($_GET['id']) ? $_GET['id'] : '';

$cat_stmt = $pdo->prepare("SELECT category_name FROM Categories WHERE category_id = ?");
$cat_stmt->bindParam(1, $cat_id);
$cat_stmt->execute();
$cat_row = $cat_stmt->fetch();
?>
<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <title>หมวดหมู่สินค้า - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .product-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      justify-items: center;
      max-width: 1000px;
      margin: 20px auto 40px;
    }

    @media (min-width: 720px) {
      .product-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (min-width: 1000px) {
      .product-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }
  </style>

</head>

<body>

  <header class="header">
    <div class="container navbar">
      <div class="brand">GEARZONE</div>
      <nav class="navlinks" aria-label="Top Links">
        <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
        <a href="/~cs6636089/GearZone/frontend/categories.html" class="active">หมวดหมู่สินค้า</a>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
        <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
        <?php endif; ?>

        <a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart" aria-label="Cart">
          <i class="fas fa-cart-shopping"></i>
        </a>
      </nav>
    </div>
  </header>

  <?php
  if ($cat_row) {
    echo "<h3 style='color:black; text-align:center; margin-top:16px;'>{$cat_row['category_name']}</h3>";
  } else {
    echo "<h3 style='color:red; text-align:center; margin-top:16px;'>ไม่พบหมวดหมู่นี้</h3>";
  }

  $stmt = $pdo->prepare("SELECT * FROM Products WHERE category_id = ?");
  $stmt->bindParam(1, $cat_id);
  $stmt->execute();
  ?>

  <div class="product-grid">
    <?php while ($row = $stmt->fetch()) { ?>
      <div style="border:1px solid #ccc; width:200px; text-align:center; background:white; padding:10px; border-radius:8px;">
        <img src="/~cs6636089/GearZone/assets/products/<?= $row['product_id'] ?>.jpg"
          width="100%" height="150"
          style="object-fit:cover; border-radius:6px;"
          onerror="this.onerror=null;this.src='/~cs6636089/GearZone/assets/sample.jpg';">

        <div style="color:black; font-weight:bold; margin-top:6px;">
          <?= $row['product_name'] ?>
        </div>
        <div style="color:red; font-weight:bold;">
          ฿<?= $row['price'] ?>
        </div>
        <div style="font-size:14px; color:gray;">
          จำนวนคงเหลือ: <?= $row['stock_quantity'] ?>
        </div>

        <form action="/~cs6636089/GearZone/backend/cart.php" method="post" style="margin-top:8px;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
          <input type="hidden" name="qty" value="1">
          <button type="submit" style="background:red; color:white; border:none; padding:8px 10px; border-radius:6px; cursor:pointer;">
            เพิ่มลงตะกร้า
          </button>
        </form>
      </div>
    <?php } ?>
  </div>



  <footer class="footer">
    <div class="footer-left">GEARZONE</div><br>
    <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
  </footer>

</body>

</html>