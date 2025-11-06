<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$db = new Database();
$products = $db->getCollection('products');

// ✅ อ่านข้อมูล JSON จาก Flutter
$data = json_decode(file_get_contents("php://input"), true);

// ✅ ตรวจสอบข้อมูลที่จำเป็น
if (!isset($data['name'], $data['price'], $data['owner_email'])) {
    echo json_encode([
        "status" => "error",
        "message" => "ข้อมูลไม่ครบ",
        "received" => $data
    ]);
    exit;
}

try {
    // ✅ ถ้ามี image ที่มาจาก Emulator (10.0.2.2) ให้เปลี่ยนเป็น IP จริงของคอม
    $imageUrl = $data['image'] ?? '';
    $imageUrl = str_replace('10.0.2.2', '10.65.155.49', $imageUrl); // 🔹 แก้ IP ที่นี่

    // ✅ แทรกข้อมูลลง MongoDB
    $insert = $products->insertOne([
        'name' => $data['name'],
        'price' => (float)$data['price'],
        'description' => $data['description'] ?? '',
        'owner_email' => $data['owner_email'],
        'image' => $imageUrl, // ✅ ใช้ URL ที่แก้แล้ว
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "เพิ่มสินค้าสำเร็จ",
        "inserted_id" => (string)$insert->getInsertedId()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
