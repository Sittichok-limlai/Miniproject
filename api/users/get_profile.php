<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $db = new Database();
    $users = $db->getCollection('users');

    $email = $_GET['email'] ?? null;
    if (!$email) {
        echo json_encode(["status" => "error", "message" => "ไม่ได้ระบุอีเมล"]);
        exit;
    }

    $user = $users->findOne(['email' => $email]);
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใช้"]);
        exit;
    }

    // ✅ แปลง ObjectId เป็น string
    $user['_id'] = (string)$user['_id'];

    // ✅ ตรวจสอบว่ามีรูปโปรไฟล์หรือไม่
    $profileUrl = '';
    if (!empty($user['profile_image'])) {
        // ✅ แก้ path ให้ถูกต้อง (เพิ่ม back-end/)
        $profileUrl = "http://10.65.155.49/website/back-end/uploads/profiles/" . $user['profile_image'];
    }

    echo json_encode([
        "status" => "success",
        "data" => [
            "_id" => $user['_id'],
            "name" => $user['name'] ?? '',
            "email" => $user['email'],
            "phone" => $user['phone'] ?? '',
            "address" => $user['address'] ?? '',
            "profile_image" => $user['profile_image'] ?? '',
            "profile_url" => $profileUrl, // ✅ มี URL เสมอ
            "created_at" => isset($user['created_at']) ? (string)$user['created_at'] : ''
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
