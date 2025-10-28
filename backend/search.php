<?php
session_start();
include "./connect.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// ดึงผลลัพธ์
$rows = [];
if ($q !== "") {
  $stmt = $pdo->prepare("SELECT product_id, product_name, price, stock_quantity 
                         FROM Products WHERE product_name LIKE ?");
  $like = "%".$q."%";
  $stmt->execute([$like]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>ผลการค้นหา - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <style>
    .wrap { 
        max-width:1100px; 
        margin:20px auto 40px; 
    }
    .result-head { 
        color:#000; 
        margin:6px 0 16px; 
    }
    .product-grid {
      display:grid; grid-template-columns:1fr; gap:20px;
      justify-items:center; max-width:1000px; margin: 0 auto;
    }
    @media(min-width:720px){ 
        .product-grid{ 
            grid-template-columns:repeat(3,1fr); 
        } 
    }
    @media(min-width:1000px){ 
        .product-grid{ 
            grid-template-columns:repeat(4,1fr); 
        } 
    }
    .card {
      border:1px solid #ccc; 
      width:220px; 
      text-align:center; 
      background:#fff;
      color:#333; 
      padding:10px; 
      border-radius:10px;
    }
    .card img { 
        width:100%; 
        height:150px; 
        object-fit:cover; 
        border-radius:6px; 
    }
    .card .name { 
        font-weight:700; 
        margin-top:6px; 
    }
    .card .price { 
        color:red; 
        font-weight:700; 
        margin-top:4px; 
    }
    .card .stock { 
        font-size:13px; 
        color:#666; 
    }
    .card form { 
        margin-top:8px; 
    }
    .btn-red {
      background:red; 
      color:#fff; 
      border:none; 
      padding:8px 10px;
      border-radius:8px; 
      cursor:pointer;
    }
    .empty { background:#fff; 
        color:#333; 
        padding:16px; 
        border-radius:10px; 
        text-align:center; 
    }
  </style>
</head>
<body>
<header class="header">
  <div class="container navbar">
    <div class="brand">GEARZONE</div>
    <div id="navbar-container"></div>
  </div>
</header>

<main class="wrap">
  <h3 class="result-head">ผลการค้นหา: "<?php echo htmlspecialchars($q ?: '-', ENT_QUOTES, 'UTF-8'); ?>"</h3>

  <?php if ($q === ""): ?>
    <div class="empty">พิมพ์คำที่ต้องการค้นหาในช่องค้นหาด้านบน แล้วกดปุ่ม "ค้นหา"</div>
  <?php elseif (!$rows): ?>
    <div class="empty">ไม่พบสินค้า</div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach($rows as $r): ?>
        <div class="card">
          <img src="/~cs6636089/GearZone/assets/products/<?php echo $r['product_id']; ?>.jpg"
               onerror="this.onerror=null;this.src='/~cs6636089/GearZone/assets/sample.jpg';" alt="">
          <div class="name"><?php echo $r['product_name']; ?></div>
          <div class="price">฿<?php echo $r['price']; ?></div>
          <div class="stock">คงเหลือ: <?php echo $r['stock_quantity']; ?></div>

          <form action="/~cs6636089/GearZone/backend/cart.php" method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo $r['product_id']; ?>">
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="btn-red">เพิ่มลงตะกร้า</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<footer class="footer">
  <div class="footer-left">GEARZONE</div><br>
  <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
</footer>


<script>
  (function(){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
      if(xhr.readyState===4 && xhr.status===200){
        document.getElementById("navbar-container").innerHTML = xhr.responseText;
      }
    };
    xhr.open("GET","/~cs6636089/GearZone/backend/navbar.php",true);
    xhr.send();
  })();
</script>
</body>
</html>
