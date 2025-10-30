<?php
session_start();
echo "<!-- Session ID: " . session_id() . " -->";

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
  $logged_in = true;
} else {
  $logged_in = false;
}

?>
<nav class="navlinks">

  <form action="/~cs6636089/GearZone/backend/search.php" method="get"
        style="display:inline-flex;gap:8px;align-items:center;margin-left:8px">
    <input type="text" name="q" placeholder="ค้นหาสินค้า..."
           style="padding:6px 8px;border-radius:8px;border:1px solid #ddd">
    <button type="submit" class="btn"
            style="padding:6px 10px;border:none;border-radius:8px;background:red;color:#fff;cursor:pointer">
      ค้นหา
    </button>
  </form>

  <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
  <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่สินค้า</a>

  <?php if ($logged_in): ?>
      <a href="/~cs6636089/GearZone/backend/my_orders.php">คำสั่งซื้อของฉัน</a>
      <a href="/~cs6636089/GearZone/backend/edit_profile.php">โปรไฟล์ของฉัน</a>
      <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart" aria-label="Cart">
        <i class="fas fa-cart-shopping"></i>
      </a>
  <?php else: ?>
      <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
      <a href="/~cs6636089/GearZone/frontend/login.html" 
         class="cart" aria-label="Cart" title="กรุณาเข้าสู่ระบบก่อน">
         <i class="fas fa-cart-shopping"></i>
      </a>
  <?php endif; ?>
</nav>
