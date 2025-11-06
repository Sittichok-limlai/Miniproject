<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

// ✅ รับค่า _id ของ order ที่จะอัปโหลดสลิป
$orderId = $_POST['_id'] ?? null;

// ✅ ตรวจสอบว่ามีการส่งไฟล์มาหรือไม่
if (!$orderId || empty($_FILES['file']['name'])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

try {
    // ✅ โฟลเดอร์เก็บไฟล์สลิป (หากไม่มีให้สร้างใหม่)
    $targetDir = __DIR__ . "/../../uploads/slips/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    // ✅ ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
    $fileName = time() . "_" . basename($_FILES['file']['name']);
    $targetPath = $targetDir . $fileName;

    // ✅ ย้ายไฟล์จาก temp ไปยังโฟลเดอร์ปลายทาง
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $db = new Database();
        $orders = $db->getCollection('orders');

        // ✅ IP จริงของคอมคุณ
        $serverIp = "10.65.155.49";

        // ✅ สร้าง URL เต็มของภาพ
        $imageUrl = "http://$serverIp/website/back-end/uploads/slips/$fileName";

        // ✅ อัปเดต slip เป็น URL เต็ม และสถานะเป็นรอตรวจสอบ
        $orders->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($orderId)],
            ['$set' => [
                'slip' => $imageUrl,
                'status' => 'รอตรวจสอบการชำระ'
            ]]
        );

        echo json_encode([
            "status" => "success",
            "message" => "อัปโหลดและบันทึก URL เต็มสำเร็จ",
            "file" => $imageUrl
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "อัปโหลดไม่สำเร็จ"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
