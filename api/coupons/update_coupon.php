<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $coupons = $db->getCollection('coupons');
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['_id'])) {
        echo json_encode(["status" => "error", "message" => "ไม่พบรหัสคูปอง"]);
        exit;
    }

    $updateFields = [
        'code' => $data['code'],
        'discount_type' => $data['discount_type'],
        'discount_value' => floatval($data['discount_value']),
        'min_order' => floatval($data['min_order']),
        'expire_date' => $data['expire_date']
    ];

    $coupons->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($data['_id'])],
        ['$set' => $updateFields]
    );

    echo json_encode(["status" => "success", "message" => "อัปเดตคูปองสำเร็จ"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
