<?php
/*
  📄 FILE: remove_from_cart.php
  📌 หน้าที่: ลบสินค้าออกจากตะกร้า โดยรับ _id จาก Flutter (JSON)
*/

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    // ✅ อ่านข้อมูลจาก JSON Body
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['_id'] ?? '';

    if (empty($id)) {
        echo json_encode([
            "status" => "error",
            "message" => "❌ ไม่พบ _id ของสินค้า"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ✅ ตรวจสอบว่าเป็น ObjectId ที่ถูกต้อง (24 ตัวอักษร hex)
    if (!preg_match('/^[0-9a-fA-F]{24}$/', $id)) {
        echo json_encode([
            "status" => "error",
            "message" => "❌ รูปแบบ ObjectId ไม่ถูกต้อง"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ✅ เชื่อมต่อฐานข้อมูล
    $db = new Database();
    $cart = $db->getCollection('cart');

    // ✅ ลบสินค้าออกจากตะกร้า
    $deleteResult = $cart->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($id)
    ]);

    if ($deleteResult->getDeletedCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "🗑️ ลบสินค้าออกจากตะกร้าแล้ว"
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "❌ ไม่พบสินค้านี้ในตะกร้า"
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    // ✅ ดักจับข้อผิดพลาดทุกประเภท
    echo json_encode([
        "status" => "error",
        "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
