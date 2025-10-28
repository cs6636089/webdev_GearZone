<?php
session_start();
include "./connect.php";

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
<?php
  // รวมราคาสินค้า
  $grand = 0;
  foreach($cart as $pid=>$it){ $grand += ($it['price'] * $it['qty']); }

  // ส่วนลดเดือนเกิด
  $birth_ok = false; $discount = 0.0;
  $u = $pdo->prepare("SELECT birthdate FROM Users WHERE user_id = ?");
  $u->execute([$user_id]);
  $ud = $u->fetch(PDO::FETCH_ASSOC);
  if ($ud && !empty($ud['birthdate'])) {
    $bd_m  = (int)date('n', strtotime($ud['birthdate']));
    $now_m = (int)date('n');
    if ($bd_m === $now_m) { $birth_ok = true; $discount = round($grand*0.12,2); }
  }

  // โปรชิ้นที่ 2 ลด 50% 
  $promo2_discount = 0.0;
  foreach ($cart as $pid => $it) {
    $q = $pdo->prepare("SELECT stock_quantity FROM Products WHERE product_id = ?");
    $q->execute([$pid]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    $stock_now = $row ? (int)$row['stock_quantity'] : 0;
    if ($stock_now > 40) {
      $pairs = intdiv((int)$it['qty'], 2);
      $promo2_discount += $pairs * 0.5 * (float)$it['price'];
    }
  }

  $final_total = max(0, $grand - $discount - $promo2_discount);
?>

<div class="line" style="font-weight:bold;">
  <div>รวมทั้งสิ้น</div>
  <div>฿<?php echo number_format($grand,2); ?></div>
</div>
<?php if ($birth_ok) { ?>
  <div class="line">
    <div>ส่วนลดเดือนเกิด 12%</div>
    <div>-฿<?php echo number_format($discount,2); ?></div>
  </div><?php } ?>
<?php if ($promo2_discount > 0) { ?>
  <div class="line"><div>โปรชิ้นที่ 2 ลด 50%</div>
  <div>-฿<?php echo number_format($promo2_discount,2); ?></div>
</div><?php } ?>
<div class="line" style="font-weight:bold;">
  <div>ยอดสุทธิ</div>
  <div>฿<?php echo number_format($final_total,2); ?></div>
</div>

<form action="/~cs6636089/GearZone/backend/place_order.php" method="post">
  <textarea name="shipping_address" rows="5" placeholder="ระบุที่อยู่จัดส่ง" required></textarea>
  <input type="hidden" name="total_amount" value="<?php echo $final_total; ?>">
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
