<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$orderId = $_GET['_id'] ?? '';

if (empty($orderId)) {
    echo json_encode(["status" => "error", "message" => "ไม่พบรหัสคำสั่งซื้อ (_id)"]);
    exit;
}

try {
    $db = new Database();
    $orders = $db->getCollection('orders');

    $order = $orders->findOne(['_id' => new MongoDB\BSON\ObjectId($orderId)]);

    if (!$order) {
        echo json_encode(["status" => "error", "message" => "ไม่พบคำสั่งซื้อ"]);
        exit;
    }

    $order['_id'] = (string)$order['_id'];
    $order['created_at'] = $order['created_at']->toDateTime()->format('Y-m-d H:i:s');

    echo json_encode(["status" => "success", "data" => $order]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
