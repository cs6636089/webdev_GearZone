<?php
session_start();
include "./connect.php";

$action = "";
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
if ($action === 'add') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
        $_SESSION['cart_flash'] = "กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าลงตะกร้า";
        header("Location: /~cs6636089/GearZone/frontend/login.html");
        exit;
    }
}

// แสดงข้อความแจ้งเตือน
function set_flash($msg)
{
    $_SESSION['cart_flash'] = $msg;
}

// Add
if ($action == "add") {
    $pid = 0;
    if (isset($_POST['product_id'])) {
        $pid = (int)$_POST['product_id'];
    }
    $qty = 1;
    if (isset($_POST['qty'])) {
        $qty = (int)$_POST['qty'];
    }
    if ($qty <= 0) $qty = 1;

    if ($pid <= 0) {
        header("Location: /~cs6636089/GearZone/backend/cart_view.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT product_id, product_name, price, stock_quantity FROM Products WHERE product_id = ?");
    $stmt->execute(array($pid));
    // array แบบ associative
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        set_flash("ไม่พบสินค้า");
        header("Location: /~cs6636089/GearZone/backend/cart_view.php");
        exit;
    }

    // ถ้ายังไม่มีในตะกร้า ให้สร้าง
    if (!isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] = array(
            "name"  => $p["product_name"],
            "price" => (float)$p["price"],
            "qty"   => 0,
            "stock" => (int)$p["stock_quantity"]
        );
    } else {
        // อัปเดต stock และราคา เผื่อมีเปลี่ยน
        $_SESSION['cart'][$pid]["stock"] = (int)$p["stock_quantity"];
        $_SESSION['cart'][$pid]["price"] = (float)$p["price"];
    }

    // เพิ่มจำนวน ไม่เกิน stock
    $newQty = $_SESSION['cart'][$pid]["qty"] + $qty;
    if ($newQty > $_SESSION['cart'][$pid]["stock"]) {
        $newQty = $_SESSION['cart'][$pid]["stock"];
        set_flash('สินค้า "' . $p["product_name"] . '" มีสต็อกไม่พอ');
    }
    $_SESSION['cart'][$pid]["qty"] = $newQty;

    set_flash('เพิ่มสินค้า "' . $p["product_name"] . '" ลงตะกร้าแล้ว');
    header("Location: /~cs6636089/GearZone/backend/cart_view.php");
    exit;
}

// Update
if ($action == "update") {
    if (isset($_POST["qty"])) {
        foreach ($_POST["qty"] as $pid => $q) {
            $pid = (int)$pid;
            $q   = (int)$q;

            if (!isset($_SESSION['cart'][$pid])) {
                continue;
            }

            if ($q <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $max = 999999;
                if (isset($_SESSION['cart'][$pid]['stock'])) {
                    $max = (int)$_SESSION['cart'][$pid]['stock'];
                }
                if ($q > $max) $q = $max;
                $_SESSION['cart'][$pid]['qty'] = $q;
            }
        }
    }
    set_flash("อัปเดตจำนวนสินค้าแล้ว");
    header("Location: /~cs6636089/GearZone/backend/cart_view.php");
    exit;
}

// Remove
if ($action == "remove") {
    $pid = 0;
    if (isset($_GET['product_id'])) {
        $pid = (int)$_GET['product_id'];
    }
    if (isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
        set_flash("นำสินค้าออกจากตะกร้าแล้ว");
    }
    header("Location: /~cs6636089/GearZone/backend/cart_view.php");
    exit;
}

// Clear
if ($action == "clear") {
    $_SESSION['cart'] = array();
    set_flash("ล้างตะกร้าสินค้าแล้ว");
    header("Location: /~cs6636089/GearZone/backend/cart_view.php");
    exit;
}

header("Location: /~cs6636089/GearZone/backend/cart_view.php");
exit;
