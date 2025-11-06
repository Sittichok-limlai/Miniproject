<?php
/*
  📄 FILE: update_order_status.php
  📌 หน้าที่: อัปเดตสถานะคำสั่งซื้อ (โดยแอดมินหรือระบบ)
  ⚙️ หากออเดอร์ถูก "ยกเลิก" จะคืนสต็อกสินค้าอัตโนมัติ
*/

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

try {
    // ✅ เชื่อมต่อฐานข้อมูล
    $db = new Database();
    $orders = $db->getCollection('orders');
    $products = $db->getCollection('products');

    // ✅ รับค่าจาก Front-end (JSON)
    $input = json_decode(file_get_contents("php://input"), true);
    $id = $input['_id'] ?? '';
    $status = $input['status'] ?? '';

    // 🔸 ตรวจสอบความถูกต้องของข้อมูล
    if (!$id || !$status) {
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
        exit;
    }

    // ✅ ดึงข้อมูลคำสั่งซื้อเดิมมาใช้ตรวจสอบ
    $order = $orders->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    if (!$order) {
        echo json_encode(["status" => "error", "message" => "ไม่พบคำสั่งซื้อนี้"]);
        exit;
    }

    // ✅ หากสถานะใหม่คือ "ยกเลิก" → คืนสต็อกสินค้า
    if ($status === 'ยกเลิก' && isset($order['items'])) {
        foreach ($order['items'] as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = intval($item['qty'] ?? 0);

            if ($productId && $qty > 0) {
                // 🔹 คืนสต็อกสินค้า
                $products->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($productId)],
                    ['$inc' => ['stock' => $qty]]
                );
            }
        }
    }

    // ✅ อัปเดตสถานะใน collection orders
    $result = $orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => ['status' => $status]]
    );

    // ✅ ตรวจสอบผลลัพธ์
    if ($result->getModifiedCount() > 0) {
        $message = ($status === 'ยกเลิก')
            ? "อัปเดตสถานะสำเร็จ และคืนสต็อกสินค้าเรียบร้อย"
            : "อัปเดตสถานะสำเร็จ";
        echo json_encode(["status" => "success", "message" => $message], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "warning", "message" => "ไม่พบคำสั่งซื้อนี้"]);
    }

} catch (Throwable $e) {
    // ❌ ดัก error ป้องกันแอป crash
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
