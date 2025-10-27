<?php
session_start();
include "../backend/connect.php";

if (isset($_SESSION['cart'])) {
  $cart = $_SESSION['cart'];   
} else {
  $cart = [];                  
}

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];   
} else {
  $user_id = 0;                  
}

?>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>เช็คเอาต์ - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <style>
    .wrap{
      max-width:1100px;
      margin:20px auto 40px;
      background:#fff;
      color:#333;
      padding:16px;
      border-radius:12px
    }
    .line{
      display:flex;
      justify-content:space-between;
      padding:6px 0;
      border-bottom:1px solid #eee
    }
    textarea,input{
      width:100%;
      padding:10px;
      border:1px solid #ccc;
      border-radius:8px;
      margin-bottom:10px
    }
    .btn{
      padding:10px 14px;
      border:none;
      border-radius:8px;
      background:red;
      color:#fff;
      cursor:pointer
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
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
      <?php endif; ?>
      <a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart"><i class="fas fa-cart-shopping"></i></a>
    </nav>
  </div>
</header>

<main class="wrap">
  <!-- <h3>เช็คเอาต์</h3> -->
  <?php
  if ($user_id == 0) {
    echo "กรุณาเข้าสู่ระบบก่อนสั่งซื้อ";
  } else if (empty($cart)) {
    echo "ยังไม่มีสินค้าในตะกร้า";
  } else {
    $grand = 0;
    foreach($cart as $pid=>$it){ $grand = $grand + ($it['price'] * $it['qty']); }
  ?>
    <h4>สรุปรายการ</h4>
    <?php foreach($cart as $pid=>$it){ ?>
      <div class="line">
        <div><?php echo $it['name']; ?> x <?php echo $it['qty']; ?></div>
        <div>฿<?php echo ($it['price']*$it['qty']); ?></div>
      </div>
    <?php } ?>
    <div class="line" style="font-weight:bold;">
      <div>รวมทั้งสิ้น</div><div>฿<?php echo $grand; ?></div>
    </div>

    <h4 style="margin-top:16px;">ที่อยู่จัดส่ง</h4>
    <form action="/~cs6636089/GearZone/backend/place_order.php" method="post">
      <textarea name="shipping_address" rows="5" placeholder="ระบุที่อยู่จัดส่ง" required></textarea>
      <input type="hidden" name="total_amount" value="<?php echo $grand; ?>">
      <button class="btn" type="submit">ยืนยันสั่งซื้อ</button>
    </form>
  <?php } ?>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
