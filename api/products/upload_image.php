<?php
require_once __DIR__ . '/../config/cors.php';

$targetDir = __DIR__ . "/uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (isset($_FILES['image'])) {
    $filename = uniqid() . "_" . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        echo json_encode([
            "status" => "success",
            "filename" => $filename,
            // ✅ เปลี่ยนเป็น 10.0.2.2 สำหรับ Emulator
            "url" => "http://10.65.155.49/website/back-end/api/products/uploads/$filename"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "อัปโหลดไม่สำเร็จ"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ไม่พบไฟล์ภาพ"]);
}
