<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "กรุณาระบุ email"]);
    exit;
}

try {
    $db = new Database();
    $orders = $db->getCollection('orders');

    $cursor = $orders->find(
        ['email' => $email],
        ['sort' => ['created_at' => -1]] // เรียงจากล่าสุดก่อน
    );

    $data = [];
    foreach ($cursor as $doc) {
        $doc['_id'] = (string)$doc['_id'];
        $doc['created_at'] = $doc['created_at']->toDateTime()->format('Y-m-d H:i:s');
        $data[] = $doc;
    }

    echo json_encode(["status" => "success", "data" => $data]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
