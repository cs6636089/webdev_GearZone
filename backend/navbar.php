<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
  $logged_in = true;
} else {
  $logged_in = false;
}

?>
<?php session_start();

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
  $logged_in = true;
} else {
  $logged_in = false;
}
?>
<nav class="navlinks">
  <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
  <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่สินค้า</a>

  <?php
  if ($logged_in) {
      echo '<a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>';
      echo '<a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart" aria-label="Cart"><i class="fas fa-cart-shopping"></i></a>';
  } else {
      echo '<a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>';
      echo '<a href="/~cs6636089/GearZone/frontend/login.html" class="cart" aria-label="Cart" title="กรุณาเข้าสู่ระบบก่อน"><i class="fas fa-cart-shopping"></i></a>';
  }
  ?>
</nav>