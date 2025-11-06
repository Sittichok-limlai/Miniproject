<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header("Content-Type: application/json; charset=UTF-8");

$db = new Database();
$messages = $db->getCollection('messages');

$sender = $_GET['sender'] ?? null;
$receiver = $_GET['receiver'] ?? null;

if (!$sender || !$receiver) {
    echo json_encode([
        "status" => "error",
        "message" => "ต้องระบุ sender และ receiver"
    ]);
    exit;
}

try {
    $cursor = $messages->find([
        '$or' => [
            ['sender' => $sender, 'receiver' => $receiver],
            ['sender' => $receiver, 'receiver' => $sender]
        ]
    ]);

    $data = [];
    foreach ($cursor as $doc) {
        $item = [
            "_id" => (string)$doc["_id"],
            "sender" => $doc["sender"] ?? "",
            "receiver" => $doc["receiver"] ?? "",
            "message" => $doc["message"] ?? "",
            "image_url" => $doc["image_url"] ?? "",  // ✅ เพิ่มบรรทัดนี้
            "type" => $doc["type"] ?? "text",
            "timestamp" => isset($doc["timestamp"]) && $doc["timestamp"] instanceof MongoDB\BSON\UTCDateTime
                ? $doc["timestamp"]->toDateTime()->format('Y-m-d H:i:s')
                : null
        ];
        $data[] = $item;
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
