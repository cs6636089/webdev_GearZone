<?php
header('Content-Type: application/json; charset=utf-8');

$data = [
  ["product_id" => 1, "product_name" => "Logitech G102 Mouse", "price" => 450],
  ["product_id" => 2, "product_name" => "Keychron K2 Keyboard", "price" => 2800],
  ["product_id" => 3, "product_name" => "HyperX Cloud II Headset", "price" => 3200],
  ["product_id" => 4, "product_name" => "TTRacing Surge", "price" => 10000]
];

// ส่งออกข้อมูลเป็น JSON string
echo json_encode($data, JSON_UNESCAPED_UNICODE);
