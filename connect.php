<?php
try {
    // 1. ติดต่อฐานข้อมูล
    $pdo = new PDO("mysql:host=localhost;dbname=168DB_47;charset=utf8", "168DB47", "Re9Tti3v");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully" . "<br><br>"; 
    //echo "<br><br>"; 
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

    // // 2. กำหนดรูปแบบคำสั่ง SQL

    // $stmt = $pdo->prepare("SELECT * FROM product");

    // // 3. ประมวลผลคำสั่ง SQL
    // $stmt->execute();

    // // 4. วนลูปดึงผลลัพธ์
    // while ($row = $stmt->fetch()) { // ดึงข ้อมูลทีละแถวเก็บไว ้ใน $row
    // echo "<pre>";
    // print_r($row); // คำสั่งแสดงค่าในอาร์ ัเรย์
    // echo "</pre>";
    // }
?>