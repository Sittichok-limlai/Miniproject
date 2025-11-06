<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    // ✅ เริ่มเชื่อมต่อฐานข้อมูล
    $db = new Database();
    $products = $db->getCollection('products');

    // ✅ รับ owner_email จาก query string (อาจไม่มี)
    $ownerEmail = $_GET['owner_email'] ?? null;

    // ✅ ถ้าไม่มี owner_email → ดึงสินค้าทั้งหมด
    $filter = [];
    if (!empty($ownerEmail)) {
        $filter['owner_email'] = $ownerEmail;
    }

    // ✅ ดึงข้อมูลสินค้า
    $cursor = $products->find($filter);

    $data = [];
    foreach ($cursor as $doc) {
        $item = [
            "_id"         => (string)$doc["_id"],
            "name"        => $doc["name"] ?? "",
            "price"       => isset($doc["price"]) ? (float)$doc["price"] : 0,
            "description" => $doc["description"] ?? "",
            "owner_email" => $doc["owner_email"] ?? "",
            "stock"       => isset($doc["stock"]) ? (int)$doc["stock"] : 0, // ✅ เพิ่มบรรทัดนี้
            "created_at"  => isset($doc["created_at"])
                ? (
                    is_object($doc["created_at"])
                        ? $doc["created_at"]->toDateTime()->format('Y-m-d H:i:s')
                        : (string)$doc["created_at"]
                )
                : null,
        ];

        // ✅ ถ้ามี image ให้เพิ่มเข้า array
        if (isset($doc["image"])) {
            $item["image"] = $doc["image"];
        }

        $data[] = $item;
    }

    // ✅ ส่งผลลัพธ์กลับ
    echo json_encode([
        "status" => "success",
        "count"  => count($data),
        "data"   => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // ❌ ดักจับข้อผิดพลาด
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
