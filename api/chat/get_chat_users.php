<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

$db = new Database();
$messages = $db->getCollection('messages');

$owner = $_GET['owner'] ?? null;

if (!$owner) {
    echo json_encode(["status" => "error", "message" => "ต้องระบุ owner"]);
    exit;
}

// ✅ ดึงรายชื่อลูกค้าที่เคยแชทกับร้าน (distinct sender)
$cursor = $messages->distinct('sender', ['receiver' => $owner]);

$data = [];
foreach ($cursor as $email) {
    if ($email !== $owner) { // ตัดชื่อเจ้าของร้านออก
        $data[] = ['customer' => $email];
    }
}

echo json_encode(["status" => "success", "data" => $data]);
?>
