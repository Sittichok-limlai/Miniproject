<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $collection = $db->getCollection('announcements');

    // ✅ รองรับ JSON หรือ Form Data
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);
    if (!$input) $input = $_POST;

    $owner_email = trim($input['owner_email'] ?? '');

    if (!$owner_email) {
        echo json_encode(["status" => "error", "message" => "ไม่พบอีเมลเจ้าของร้าน"]);
        exit;
    }

    // ✅ ดึงประกาศเฉพาะของเจ้าของร้าน
    $docs = $collection->find(["owner_email" => $owner_email]);
    $announcements = [];
    foreach ($docs as $d) {
        $announcements[] = [
            "_id" => (string)$d["_id"],
            "title" => $d["title"] ?? "",
            "message" => $d["message"] ?? "",
            "owner_email" => $d["owner_email"] ?? "",
            "created_at" => $d["created_at"] ?? ""
        ];
    }

    echo json_encode([
        "status" => "success",
        "announcements" => $announcements
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
