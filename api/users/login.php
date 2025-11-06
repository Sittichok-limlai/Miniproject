<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $db = new Database();
    $users = $db->getCollection('users');

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email'], $data['password'])) {
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // 🔍 หาผู้ใช้ในฐานข้อมูล
    $user = $users->findOne(['email' => $email]);
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "ไม่พบบัญชีผู้ใช้นี้"]);
        exit;
    }

    // ✅ ตรวจสอบรหัสผ่าน
    if (!password_verify($password, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
        exit;
    }

    // ✅ ตรวจสอบว่ามีรูปโปรไฟล์ไหม
    $profileUrl = null;
    if (!empty($user['profile_image'])) {
        $profileUrl = "http://10.65.155.49/website/uploads/profiles/" . $user['profile_image'];
    }

    // ✅ ส่งข้อมูลกลับให้ Flutter ใช้แสดงได้ทันที
    echo json_encode([
        "status" => "success",
        "message" => "เข้าสู่ระบบสำเร็จ",
        "data" => [
            "_id" => (string)$user['_id'],
            "name" => $user['name'] ?? '',
            "email" => $user['email'],
            "role" => $user['role'] ?? 'customer',
            "phone" => $user['phone'] ?? '',
            "address" => $user['address'] ?? '',
            "profile_image" => $user['profile_image'] ?? '',
            "profile_url" => $profileUrl,
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
