<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $db = new Database();
    $users = $db->getCollection('users');

    // ✅ รองรับ JSON และ multipart
    $email = $_POST['email'] ?? null;
    $raw = [];
    if (!$email) {
        $raw = json_decode(file_get_contents('php://input'), true);
        $email = $raw['email'] ?? null;
    }

    if (!$email) {
        echo json_encode(["status" => "error", "message" => "กรุณาระบุ email"]);
        exit;
    }

    // ✅ อ่านข้อมูลทั่วไป
    $name    = $_POST['name']    ?? ($raw['name']    ?? null);
    $phone   = $_POST['phone']   ?? ($raw['phone']   ?? null);
    $address = $_POST['address'] ?? ($raw['address'] ?? null);

    $set = [];
    if ($name !== null)    $set['name']    = $name;
    if ($phone !== null)   $set['phone']   = $phone;
    if ($address !== null) $set['address'] = $address;

    // ✅ จัดการรูปโปรไฟล์ (แก้ path ให้อัปโหลดตรงโฟลเดอร์จริง)
    if (!empty($_FILES['profile_image']['name'])) {
        // 👉 ใช้ path เต็มตรงนี้เลย
        $uploadDir = realpath(__DIR__ . '/../../uploads/profiles');

        // ถ้ายังไม่มีให้สร้างใหม่
        if ($uploadDir === false || !is_dir($uploadDir)) {
            $uploadDir = __DIR__ . '/../../uploads/profiles';
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $newName = 'pf_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($safeExt ? ".$safeExt" : '');
        $target = $uploadDir . DIRECTORY_SEPARATOR . $newName;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
            echo json_encode(["status" => "error", "message" => "อัปโหลดรูปไม่สำเร็จ"]);
            exit;
        }

        $set['profile_image'] = $newName;
    }

    if (empty($set)) {
        echo json_encode(["status" => "error", "message" => "ไม่มีข้อมูลสำหรับอัปเดต"]);
        exit;
    }

    // ✅ ตรวจว่ามีผู้ใช้อยู่หรือไม่
    $result = $users->updateOne(['email' => $email], ['$set' => $set]);
    if ($result->getMatchedCount() === 0) {
        echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใช้ในระบบ"]);
        exit;
    }

    // ✅ ดึงข้อมูลล่าสุดส่งกลับ
    $user = $users->findOne(['email' => $email]);
    if ($user) {
        $user['_id'] = (string)$user['_id'];
        if (!empty($user['profile_image'])) {
            $user['profile_url'] = "http://10.65.155.49/website/back-end/uploads/profiles/" . $user['profile_image'];
        } else {
            $user['profile_url'] = "http://10.65.155.49/website/back-end/uploads/profiles/default_user.png";
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "อัปเดตโปรไฟล์สำเร็จ",
        "data" => $user
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
