<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

header('Content-Type: application/json; charset=UTF-8');

$sender = $_POST['sender'] ?? '';
$receiver = $_POST['receiver'] ?? '';

if (empty($sender) || empty($receiver)) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(["status" => "error", "message" => "ไม่พบไฟล์"]);
    exit;
}

try {
    // 📂 เปลี่ยน path เป็นโฟลเดอร์ chat/uploads
    $uploadDir = __DIR__ . '/../chat/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ✅ ตั้งชื่อไฟล์ใหม่ ป้องกันชื่อซ้ำ
    $originalName = basename($_FILES['file']['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $fileName = time() . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {

        // ✅ เปลี่ยน IP ให้เป็น IP จริงของเครื่องคุณ
        $serverIp = "10.65.155.49"; // ← ตรวจจาก ipconfig (Wireless LAN adapter Wi-Fi)

        // ✅ ใช้ URL เต็มสำหรับ Flutter (มือถือมองเห็นได้)
        $imageUrl = "http://$serverIp/website/back-end/api/chat/uploads/$fileName";

        // 💾 บันทึกลง MongoDB
        $db = new Database();
        $collection = $db->getCollection('messages');

        $collection->insertOne([
            'sender'    => $sender,
            'receiver'  => $receiver,
            'message'   => '',
            'image_url' => $imageUrl,  // ✅ URL เต็ม
            'type'      => 'image',
            'timestamp' => new MongoDB\BSON\UTCDateTime()
        ]);

        echo json_encode([
            "status" => "success",
            "image_url" => $imageUrl
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่สามารถอัปโหลดไฟล์ได้"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
