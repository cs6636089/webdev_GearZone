<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  $_SESSION['cart_flash'] = "กรุณาเข้าสู่ระบบก่อนเปิดตะกร้า";
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

if (isset($_SESSION['cart_flash'])) {
    $flash = $_SESSION['cart_flash'];
    unset($_SESSION['cart_flash']);
} else {
    $flash = '';
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>ตะกร้าสินค้า - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <style>
    .cart-container{
      max-width:1100px;
      margin:20px auto 40px;
      padding:0 10px
    }
    .flash{
      background:#fff;
      color:#333;
      border-left:6px solid red;
      padding:10px 14px;
      border-radius:8px;
      margin:16px 0
    }
    .cart-empty{
      background:#fff;
      color:#333;
      padding:20px;
      border-radius:10px;
      text-align:center
    }
    table.cart{
      width:100%;
      border-collapse:collapse;
      background:#fff;
      color:#333;
      border-radius:12px;
      overflow:hidden
    }
    table.cart th,table.cart td{
      padding:12px;
      border-bottom:1px solid #eee;
      text-align:left
    }
    table.cart th{
      background:#f6f6f6
    }
    .qty-input{
      width:60px;
      text-align:center;
      padding:6px
    }
    .thumb{
      width:80px;
      height:60px;
      object-fit:cover;
      border-radius:6px
    }
    .actions{
      display:flex;
      gap:10px;
      justify-content:flex-end;
      margin-top:14px;
      flex-wrap:wrap
    }
    .btn{
      display:inline-block;
      padding:10px 14px;
      border-radius:8px;
      border:none;
      cursor:pointer
    }
    .btn-red{
      background:red;
      color:#fff
    }
    .btn-ghost{
      background:#fff;
      color:#333;
      border:1px solid #ddd
    }.
    btn-gray{
      background:black;
      color:#fff
    }
    .total-box{
      margin-top:16px;
      background:#fff;
      color:#333;
      padding:16px;
      border-radius:12px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px
    }
    .total-box strong{
      color:red;
      font-size:18px
    }.note{color:#777;
      font-size:14px;
      margin-top:6px
    }
    @media (max-width:719px){
        table.cart thead{
          display:none
        }
        table.cart tr{
          display:block;
          background:#fff;
          margin-bottom:14px;
          border-radius:10px;
          padding:10px
        }
      table.cart td{
        display:block;
        width:100%
      }
      table.cart td img.thumb{
        width:100%;
        height:auto;
        max-width:200px
      }
      table.cart td+td{
        margin-top:8px
      }
      .total-box{
        flex-direction:column;
        align-items:flex-start
      }
      .actions{
        justify-content:center
      }
    }
    @media (min-width:720px){
      .cart-container{
        padding:0 20px
      }
    }
    @media (min-width:1000px){
      .cart-container{
        max-width:1100px
      }
      table.cart td img.thumb{
        width:80px;
        height:60px
      }
      .actions{
        justify-content:flex-end
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
      <a href="/~cs6636089/GearZone/frontend/categories.html">หมวดหมู่สินค้า</a>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) { ?>
        <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
      <?php } else { ?>
        <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
      <?php } ?>
      <a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart" aria-label="Cart"><i class="fas fa-cart-shopping"></i></a>
    </nav>
  </div>
</header>

<main class="cart-container">
  <h3 style="color:black;text-align:center;">ตะกร้าสินค้า</h3>

  <?php if ($flash != '') { ?><div class="flash"><?php echo $flash; ?></div><?php } ?>

  <?php if (empty($cart)) { ?>
    <div class="cart-empty">
      ยังไม่มีสินค้าในตะกร้า<br>
      <a class="btn btn-red" href="/~cs6636089/GearZone/frontend/categories.html" style="margin-top:10px;display:inline-block;">เลือกซื้อสินค้า</a>
    </div>
  <?php } else { ?>

  <?php
    // คำนวณยอดรวม + ส่วนลด 
    $grand = 0.0;
    foreach ($cart as $pid => $item) {
      $grand += ((float)$item['price'] * (int)$item['qty']);
    }

    // ส่วนลดเดือนเกิด 12%
    $birth_ok = false;
    $bday_discount = 0.0;
    $u = $pdo->prepare("SELECT birthdate FROM Users WHERE user_id = ?");
    $u->execute([$_SESSION['user_id']]);
    $ud = $u->fetch(PDO::FETCH_ASSOC);
    if ($ud && !empty($ud['birthdate'])) {
      $bd_m  = (int)date('n', strtotime($ud['birthdate']));
      $now_m = (int)date('n');
      if ($bd_m === $now_m) {
        $birth_ok = true;
        $bday_discount = round($grand * 0.12, 2);
      }
    }

    // โปรชิ้นที่ 2 ลด 50% 
    $promo2_discount = 0.0;
    foreach ($cart as $pid => $it) {
      $q = $pdo->prepare("SELECT stock_quantity FROM Products WHERE product_id = ?");
      $q->execute([$pid]);
      $row = $q->fetch(PDO::FETCH_ASSOC);
      $stock_now = $row ? (int)$row['stock_quantity'] : 0;

      if ($stock_now > 40) {
        $pairs = intdiv((int)$it['qty'], 2);                 // จำนวนคู่
        $promo2_discount += $pairs * 0.5 * (float)$it['price']; // ลดครึ่งราคาต่อ 1 คู่
      }
    }

    $final_total = max(0, $grand - $bday_discount - $promo2_discount);
  ?>

  <form action="/~cs6636089/GearZone/backend/cart.php" method="post">
    <input type="hidden" name="action" value="update">
    <table class="cart">
      <tbody>
        <?php foreach ($cart as $pid => $item) {
          $sub = (float)$item['price'] * (int)$item['qty']; ?>
          <tr>
            <td>
              <div style="display:flex;gap:12px;align-items:center;">
                <img class="thumb" src="/~cs6636089/GearZone/assets/products/<?php echo $pid; ?>.jpg">
                <div>
                  <div style="font-weight:700;"><?php echo htmlspecialchars($item['name']); ?></div>
                  <div class="note">สต็อก: <?php echo (int)$item['stock']; ?> ชิ้น</div>
                </div>
              </div>
            </td>
            <td>฿<?php echo number_format($item['price'], 2); ?></td>
            <td>
              <input class="qty-input" type="number" min="1" max="<?php echo (int)$item['stock']; ?>"
                     name="qty[<?php echo $pid; ?>]" value="<?php echo (int)$item['qty']; ?>">
            </td>
            <td>฿<?php echo number_format($sub, 2); ?></td>
            <td><a class="btn btn-ghost" href="/~cs6636089/GearZone/backend/cart.php?action=remove&product_id=<?php echo $pid; ?>">ลบ</a></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <div class="total-box">
      <div>
        <div>รวมสินค้า (Subtotal): <strong>฿<?php echo number_format($grand, 2); ?></strong></div>
        <?php if ($birth_ok) { ?>
          <div class="note" style="color:#d00;">ส่วนลดเดือนเกิด 12%: -฿<?php echo number_format($bday_discount, 2); ?></div>
        <?php } ?>
        <?php if ($promo2_discount > 0) { ?>
          <div class="note" style="color:#d00;">โปรชิ้นที่ 2 ลด 50%: -฿<?php echo number_format($promo2_discount, 2); ?></div>
        <?php } ?>
        <div class="note" style="font-weight:bold;color:#000;">ยอดสุทธิ: ฿<?php echo number_format($final_total, 2); ?></div>
      </div>

      <div class="actions">
        <a class="btn btn-ghost" href="/~cs6636089/GearZone/frontend/categories.html">เลือกซื้อเพิ่ม</a>
        <a class="btn btn-ghost" href="/~cs6636089/GearZone/backend/cart.php?action=clear">ล้างตะกร้า</a>
        <button type="submit" class="btn btn-red">อัปเดตจำนวน</button>
        <a class="btn btn-red" href="/~cs6636089/GearZone/backend/checkout.php">ไปชำระเงิน</a>
      </div>
    </div>
  </form>
  <?php } ?>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>
</body>
</html>
