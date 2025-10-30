<?php
session_start();
include "./connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
  header("Location: /~cs6636089/GearZone/frontend/login.html");
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, phone, address FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['username' => '', 'phone' => '', 'address' => ''];
?>

<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <title>แก้ไขข้อมูลส่วนตัว - GearZone</title>
  <link rel="stylesheet" href="/~cs6636089/GearZone/frontend/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <style>
    .profile-wrap {
      max-width: 720px;
      margin: 40px auto 60px;
      padding: 0 16px;
    }

    .profile-card {
      background: #fff;
      color: #222;
      border-radius: 16px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, .12);
      border: 1px solid #eee;
      padding: 22px;
    }

    .profile-title {
      margin: 0 0 14px;
      font-size: 28px;
      font-weight: 700;
      color: #d00;
      text-align: center;
      letter-spacing: .5px;
    }

    .profile-sub {
      margin: 0 0 18px;
      color: #666;
      text-align: center;
      font-size: 14px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    @media (max-width:720px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }

    .form-item label {
      display: block;
      font-size: 14px;
      color: #555;
      margin-bottom: 6px;
    }

    .form-item input[type="text"],
    .form-item input[type="tel"],
    .form-item input[type="password"],
    .form-item textarea {
      width: 100%;
      background: #fafafa;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 12px;
      font-size: 15px;
      color: #222;
      transition: border .15s, box-shadow .15s, background .15s;
    }

    .form-item textarea {
      min-height: 110px;
      resize: vertical;
    }

    .form-item input:focus,
    .form-item textarea:focus {
      outline: none;
      border-color: #ff4b3a;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(255, 75, 58, .12);
    }

    .hint {
      font-size: 12px;
      color: #888;
      margin-top: 4px;
    }

    .actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 16px;
    }

    .btn {
      padding: 12px 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 700;
    }

    .btn-red {
      background: #ff3c2e;
      color: #fff;
    }

    .btn-ghost {
      background: #fff;
      color: #333;
      border: 1px solid #ddd;
    }

    .btn:hover {
      transform: translateY(-1px);
    }
  </style>
</head>

<body>

  <header class="header">
    <div class="container navbar">
      <div class="brand">GEARZONE</div>
      <nav class="navlinks">
        <a href="/~cs6636089/GearZone/index.html">หน้าหลัก</a>
        <a href="/~cs6636089/GearZone/backend/my_orders.php">คำสั่งซื้อของฉัน</a>
        <a href="/~cs6636089/GearZone/backend/edit_profile.php" class="active">โปรไฟล์ของฉัน</a>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0): ?>
          <a href="/~cs6636089/GearZone/backend/logout.php">ออกจากระบบ</a>
        <?php else: ?>
          <a href="/~cs6636089/GearZone/frontend/login.html">เข้าสู่ระบบ</a>
        <?php endif; ?>
        <a href="/~cs6636089/GearZone/backend/cart_view.php" class="cart" aria-label="Cart"><i class="fas fa-cart-shopping"></i></a>
      </nav>
    </div>
  </header>

  <main class="profile-wrap">
    <div class="profile-card">
      <h1 class="profile-title">แก้ไขข้อมูลส่วนตัว</h1>
      <p class="profile-sub">อัปเดตชื่อผู้ใช้ เบอร์โทรศัพท์ ที่อยู่ และรหัสผ่านใหม่ได้ที่นี่</p>

      <form action="/~cs6636089/GearZone/backend/update_profile.php" method="post" autocomplete="off">
        <div class="form-grid">
          <div class="form-item">
            <label>ชื่อผู้ใช้</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
          </div>

          <div class="form-item">
            <label>เบอร์โทรศัพท์</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
          </div>

          <div class="form-item" style="grid-column:1 / -1">
            <label>ที่อยู่</label>
            <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
          </div>

          <div class="form-item" style="grid-column:1 / -1">
            <label>รหัสผ่านใหม่ <span class="hint">(ถ้าไม่เปลี่ยนให้เว้นว่าง)</span></label>
            <input type="password" name="new_password" placeholder="••••••••">
          </div>
        </div>

        <div class="actions">
          <a class="btn btn-ghost" href="/~cs6636089/GearZone/index.html">ยกเลิก</a>
          <button type="submit" class="btn btn-red">บันทึกการแก้ไข</button>
        </div>
      </form>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-left">GEARZONE</div><br>
    <div class="footer-right">&copy; 2025 GearZone. สงวนลิขสิทธิ์.</div>
  </footer>

</body>

</html>