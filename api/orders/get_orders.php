<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $orders = $db->getCollection('orders');

    // ✅ ดึงข้อมูลทั้งหมดเรียงจากใหม่ไปเก่า
    $cursor = $orders->find([], ['sort' => ['created_at' => -1]]);
    $data = [];

    // ✅ ใช้ IP จริงของเครื่องคุณ (แก้ตาม IP ใน network ของคุณ)
    $serverIp = "10.65.155.49";

    foreach ($cursor as $doc) {
        // ✅ เตรียมข้อมูลออเดอร์พื้นฐาน
        $order = [
            "_id" => (string)($doc["_id"] ?? ""),
            "email" => $doc["email"] ?? "",
            "total" => $doc["total"] ?? 0,
            "status" => $doc["status"] ?? "",
            "created_at" => isset($doc["created_at"]) && $doc["created_at"] instanceof MongoDB\BSON\UTCDateTime
                ? $doc["created_at"]->toDateTime()->format('Y-m-d H:i:s')
                : null,
        ];

        // ✅ ถ้ามี slip → ต่อ URL เต็มให้
        if (!empty($doc["slip"])) {
            $slip = $doc["slip"];

            // ถ้าเป็น URL เต็มอยู่แล้ว
            if (strpos($slip, "http") === 0) {
                $slipFullUrl = $slip;
            } else {
                // ถ้าเป็นชื่อไฟล์ → ต่อ path เต็มให้
                $slipFullUrl = "http://$serverIp/website/back-end/uploads/slips/" . $slip;
            }

            // ✅ เก็บทั้งชื่อไฟล์และ URL เต็ม
            $order["slip"] = $slip;
            $order["slip_url"] = $slipFullUrl;
        } else {
            $order["slip"] = null;
            $order["slip_url"] = null;
        }

        $data[] = $order;
    }

    // ✅ ส่งข้อมูลกลับเป็น JSON
    echo json_encode([
        "status" => "success",
        "data" => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // ❌ กรณีเกิดข้อผิดพลาด
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
