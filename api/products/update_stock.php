<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $products = $db->getCollection('products');

    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['_id'] ?? '';
    $stock = intval($data['stock'] ?? 0);

    if (empty($id)) {
        echo json_encode(["status" => "error", "message" => "รหัสสินค้าไม่ถูกต้อง"]);
        exit;
    }

    $result = $products->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => ['stock' => $stock]]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(["status" => "success", "message" => "อัปเดตสต๊อกสำเร็จ"]);
    } else {
        echo json_encode(["status" => "warning", "message" => "ไม่พบสินค้าหรือค่าเท่าเดิม"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
