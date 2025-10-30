<?php
session_start();
include "./connect.php";

$sql = "SELECT product_id, product_name, price, stock_quantity
        FROM Products WHERE stock_quantity > 40
        ORDER BY stock_quantity DESC LIMIT 12";
$stmt = $pdo->query($sql);

?>
<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
  <article class="promo-card">
    <span class="ribbon">โปร: ชิ้นที่ 2 ลด 50%</span>

    <div class="thumb">
      <img
        src="/~cs6636089/GearZone/assets/products/<?php echo (int)$row['product_id']; ?>.jpg"
        alt="<?php echo htmlspecialchars($row['product_name']); ?>" loading="lazy">
    </div>

    <div class="meta">
      <h4 class="title"><?php echo htmlspecialchars($row['product_name']); ?></h4>
      <div class="muted">คงเหลือ: <?php echo (int)$row['stock_quantity']; ?> ชิ้น</div>

      <div class="price-row">
        <div class="price">฿<?php echo number_format($row['price'], 0); ?></div>

        <?php if (!empty($_SESSION['user_id'])): ?>
          <form action="/~cs6636089/GearZone/backend/cart.php" method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="btn-buy">สั่งซื้อ</button>
          </form>
        <?php else: ?>
          <a class="btn-buy" href="/~cs6636089/GearZone/frontend/login.html" title="กรุณาเข้าสู่ระบบก่อน">สั่งซื้อ</a>
        <?php endif; ?>

      </div>
    </div>
  </article>
<?php } ?>