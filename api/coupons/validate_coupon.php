<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $coupons = $db->getCollection('coupons');

    $code = $_GET['code'] ?? '';
    if (empty($code)) {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกรหัสคูปอง"]);
        exit;
    }

    $coupon = $coupons->findOne(['code' => $code]);
    if (!$coupon) {
        echo json_encode(["status" => "error", "message" => "ไม่พบคูปองนี้"]);
        exit;
    }

    // แปลง ObjectId และวันที่ให้เป็น String
    $coupon['_id'] = (string)$coupon['_id'];
    if (isset($coupon['expire_date'])) {
        $coupon['expire_date'] = (string)$coupon['expire_date'];
    }

    echo json_encode(["status" => "success", "data" => $coupon]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
