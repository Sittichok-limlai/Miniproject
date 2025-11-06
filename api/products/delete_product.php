<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$db = new Database();
$products = $db->getCollection('products');

// ✅ รับข้อมูลจาก Flutter
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['_id'])) {
    echo json_encode(["status" => "error", "message" => "ต้องระบุ _id"]);
    exit;
}

try {
    $result = $products->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($data['_id'])
    ]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode(["status" => "success", "message" => "ลบสินค้าสำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบสินค้าที่จะลบ"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
