<?php
/*
  📄 FILE: create_order.php
  📌 หน้าที่: สร้างคำสั่งซื้อ / บันทึกออเดอร์ และหักสต็อกสินค้าอย่างปลอดภัย
  ⚙️ ถ้าสต็อกไม่พอหรือหมด จะไม่อนุญาตให้สั่งซื้อ
*/

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    // ✅ เชื่อมต่อฐานข้อมูล
    $db = new Database();
    $orders = $db->getCollection('orders');
    $products = $db->getCollection('products');
    $cart = $db->getCollection('cart');

    // ✅ รับค่าจาก Flutter (ผ่าน POST)
    $email = $_POST['email'] ?? '';
    $total = floatval($_POST['total'] ?? 0);
    $status = 'รอตรวจสอบการชำระ';
    $slipName = '';

    // 🔹 ตรวจสอบข้อมูลเบื้องต้น
    if (!$email || $total <= 0) {
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
        exit;
    }

    // ✅ ตรวจสอบหรือสร้างโฟลเดอร์เก็บสลิป
    $targetDir = __DIR__ . "/../../uploads/slips/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    // ✅ จัดการอัปโหลดสลิป
    if (!empty($_FILES['slip']['name'])) {
        $fileName = time() . "_" . basename($_FILES["slip"]["name"]);
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["slip"]["tmp_name"], $targetPath)) {
            $slipName = $fileName;
        } else {
            echo json_encode(["status" => "error", "message" => "อัปโหลดสลิปไม่สำเร็จ"]);
            exit;
        }
    }

    // ✅ เก็บข้อมูลสินค้าในออเดอร์
    $orderedProducts = [];
    $cartJson = $_POST['cart_items'] ?? null;

    // ✅ รองรับทั้งแบบส่ง cart_items จาก Flutter หรือดึงจาก DB
    if ($cartJson) {
        $cartItems = json_decode($cartJson, true);
        if (!is_array($cartItems)) {
            echo json_encode(["status" => "error", "message" => "ข้อมูลสินค้าไม่ถูกต้อง"]);
            exit;
        }

        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = intval($item['qty'] ?? 1);

            if ($productId) {
                $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
                if (!$product) continue;

                // ✅ ตรวจสอบสต็อกก่อนหัก
                if (!isset($product['stock']) || $product['stock'] <= 0) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "❌ สินค้า {$product['name']} หมดแล้ว"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                if ($qty > $product['stock']) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "❌ สินค้า {$product['name']} มีไม่พอ (เหลือ {$product['stock']})"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // ✅ หักสต็อกสินค้า
                $products->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($productId)],
                    ['$inc' => ['stock' => -$qty]]
                );

                $orderedProducts[] = [
                    "product_id" => $productId,
                    "product_name" => $product['name'] ?? '',
                    "qty" => $qty,
                    "price" => $product['price'] ?? 0
                ];
            }
        }

        // ✅ ล้างตะกร้าในฐานข้อมูลหลังชำระเงิน
        $cart->deleteMany(['email' => $email]);
    } else {
        // ✅ ดึงจากตะกร้าในฐานข้อมูล
        $cartItems = $cart->find(['email' => $email]);
        foreach ($cartItems as $item) {
            $productId = (string)$item['product_id'];
            $qty = intval($item['qty'] ?? 1);

            $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
            if (!$product) continue;

            // ✅ ตรวจสอบสต็อกก่อนหัก
            if (!isset($product['stock']) || $product['stock'] <= 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "❌ สินค้า {$product['name']} หมดแล้ว"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($qty > $product['stock']) {
                echo json_encode([
                    "status" => "error",
                    "message" => "❌ สินค้า {$product['name']} มีไม่พอ (เหลือ {$product['stock']})"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // ✅ หักสต็อก
            $products->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($productId)],
                ['$inc' => ['stock' => -$qty]]
            );

            $orderedProducts[] = [
                "product_id" => $productId,
                "product_name" => $item['product_name'] ?? '',
                "qty" => $qty,
                "price" => $item['price'] ?? 0
            ];
        }

        // ✅ ล้างตะกร้าในฐานข้อมูล
        $cart->deleteMany(['email' => $email]);
    }

    // ✅ เพิ่มข้อมูลออเดอร์ใหม่
    $orders->insertOne([
        "email" => $email,
        "items" => $orderedProducts,
        "total" => $total,
        "status" => $status,
        "slip" => $slipName,
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "สร้างออเดอร์สำเร็จ ✅ ล้างตะกร้าและอัปเดตสต็อกเรียบร้อย",
        "slip" => $slipName
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // ❌ ดัก error ป้องกันแอป crash
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
