<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

$db = new Database();
$users = $db->getCollection('users');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name'], $data['email'], $data['password'])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

$name = $data['name'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$role = $data['role'] ?? 'customer'; // ✅ ถ้าไม่ส่งมา ถือเป็นลูกค้า

$existing = $users->findOne(['email' => $email]);
if ($existing) {
    echo json_encode(["status" => "error", "message" => "อีเมลนี้ถูกใช้แล้ว"]);
    exit;
}

$users->insertOne([
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'role' => $role, // ✅ เพิ่ม role ลงในฐานข้อมูล
    'created_at' => new MongoDB\BSON\UTCDateTime(),
]);

echo json_encode(["status" => "success", "message" => "สมัครสมาชิกสำเร็จ"]);
?>
