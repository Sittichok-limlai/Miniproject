<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $db = new Database();
    $collection = $db->getCollection('announcements');

    // ✅ รับ JSON หรือ POST ทั้งคู่ได้
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    // ถ้า decode ไม่ได้ (อาจมาจาก form-data)
    if (!$input) {
        $input = $_POST;
    }

    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');
    $owner_email = trim($input['owner_email'] ?? '');

    // 🧩 Debug ช่วยเช็คใน Log ได้ว่าค่าเข้ามาไหม
    error_log("DEBUG_PHP title=$title message=$message email=$owner_email");

    if (!$title || !$message || !$owner_email) {
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
        exit;
    }

    // ✅ เพิ่มลง MongoDB
    $newData = [
        "title" => $title,
        "message" => $message,
        "owner_email" => $owner_email,
        "created_at" => date('Y-m-d H:i:s')
    ];

    $collection->insertOne($newData);

    echo json_encode(["status" => "success", "message" => "บันทึกประกาศเรียบร้อย"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
