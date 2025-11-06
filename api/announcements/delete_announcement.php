<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $collection = $db->getCollection('announcements');
    $id = $_POST['id'] ?? '';

    if (!$id) {
        echo json_encode(["status" => "error", "message" => "ไม่พบ ID ของประกาศ"]);
        exit;
    }

    $result = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode(["status" => "success", "message" => "ลบประกาศสำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบประกาศที่ต้องการลบ"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
