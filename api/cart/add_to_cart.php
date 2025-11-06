<?php
/*
  📄 FILE: add_to_cart.php
  📌 หน้าที่: เพิ่มสินค้าลงตะกร้า โดยดึง _id จาก products เพื่อให้เชื่อมโยงได้ถูกต้อง
*/

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    // ✅ รับข้อมูลจาก Flutter
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $product_name = $data['product_name'] ?? '';
    $price = floatval($data['price'] ?? 0);
    $qty = intval($data['qty'] ?? 1);

    // ✅ ตรวจสอบข้อมูลเบื้องต้น
    if (!$email || !$product_name) {
        echo json_encode([
            "status" => "error",
            "message" => "ข้อมูลไม่ครบ"
        ]);
        exit;
    }

    // ✅ เริ่มเชื่อมต่อฐานข้อมูล
    $db = new Database();
    $cart = $db->getCollection('cart');
    $products = $db->getCollection('products');

    // ✅ ค้นหาสินค้าจากชื่อ (ไม่สนตัวพิมพ์เล็ก-ใหญ่)
    $product = $products->findOne([
        'name' => ['$regex' => '^' . preg_quote($product_name) . '$', '$options' => 'i']
    ]);

    if (!$product) {
        echo json_encode([
            "status" => "error",
            "message" => "❌ ไม่พบสินค้านี้ในฐานข้อมูล"
        ]);
        exit;
    }

    // ✅ ดึง ObjectId ของสินค้า
    $productId = $product['_id'];

    // ✅ ตรวจว่าผู้ใช้นี้มีสินค้านี้อยู่ในตะกร้าแล้วหรือยัง
    $existing = $cart->findOne([
        'email' => $email,
        'product_id' => $productId
    ]);

    if ($existing) {
        // ถ้ามีอยู่แล้ว → เพิ่มจำนวน
        $cart->updateOne(
            ['_id' => $existing->_id],
            ['$inc' => ['qty' => $qty]]
        );
        $message = "เพิ่มจำนวนสินค้าในตะกร้าแล้ว";
    } else {
        // ถ้ายังไม่มี → เพิ่มสินค้าใหม่
        $cart->insertOne([
            'email' => $email,
            'product_id' => $productId, // ✅ ใช้ ObjectId จาก products
            'product_name' => $product_name,
            'price' => $price,
            'qty' => $qty,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $message = "เพิ่มสินค้าลงตะกร้าเรียบร้อย";
    }

    // ✅ ส่งผลลัพธ์กลับไปยังแอป
    echo json_encode([
        "status" => "success",
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // ❌ กรณีเกิดข้อผิดพลาด
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
