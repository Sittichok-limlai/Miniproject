<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$db = new Database();
$products = $db->getCollection('products');

// ✅ รับข้อมูลจาก Flutter
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['_id'], $data['name'], $data['price'])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

try {
    $result = $products->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($data['_id'])],
        ['$set' => [
            'name' => $data['name'],
            'price' => (float)$data['price'],
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(["status" => "success", "message" => "อัปเดตสินค้าสำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบสินค้าหรือไม่มีการเปลี่ยนแปลง"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
