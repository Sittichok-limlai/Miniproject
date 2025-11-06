<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

$db = new Database();
$messages = $db->getCollection('messages');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['sender'], $data['receiver'], $data['message'])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

$messages->insertOne([
    'sender' => $data['sender'],
    'receiver' => $data['receiver'],
    'message' => $data['message'],
    'timestamp' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(["status" => "success", "message" => "ส่งข้อความสำเร็จ"]);
?>
