<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $orders = $db->getCollection('orders');

    // ✅ ดึงคำสั่งซื้อทั้งหมด เรียงจากใหม่สุด
    $cursor = $orders->find([], ['sort' => ['created_at' => -1]]);
    $data = [];

    // ✅ ใช้ IP จริงของเครื่อง (มือถือจะเข้าถึงได้)
    $serverIp = "10.65.155.49";

    foreach ($cursor as $doc) {
        $order = [
            "_id" => (string)($doc["_id"] ?? ""),
            "email" => $doc["email"] ?? "",
            "total" => $doc["total"] ?? 0,
            "status" => $doc["status"] ?? "",
            "created_at" => isset($doc["created_at"]) && $doc["created_at"] instanceof MongoDB\BSON\UTCDateTime
                ? $doc["created_at"]->toDateTime()->format('Y-m-d H:i:s')
                : null,
        ];

        // ✅ ตรวจสอบและต่อ URL ของสลิปให้ถูกต้อง
        if (!empty($doc["slip"])) {
            $slipFile = $doc["slip"];
            if (strpos($slipFile, "http") === 0) {
                // ถ้าเป็น URL เต็มอยู่แล้ว
                $order["slip_url"] = $slipFile;
            } else {
                // ถ้าเป็นชื่อไฟล์ → ต่อให้เป็น URL เต็ม
                $order["slip_url"] = "http://$serverIp/website/back-end/uploads/slips/" . $slipFile;
            }
        } else {
            $order["slip_url"] = null;
        }

        $data[] = $order;
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
