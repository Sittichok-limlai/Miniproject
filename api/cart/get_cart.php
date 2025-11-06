<?php
/*
  📄 FILE: get_cart.php
  📌 หน้าที่: ดึงข้อมูลสินค้าทั้งหมดในตะกร้าของผู้ใช้
  ⚙️ แก้ไขให้ join กับ products ได้แม้ product_id ถูกเก็บเป็น string
  ✅ ใช้ชื่อฟิลด์จริง (name, price, stock)
*/

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    // ✅ เชื่อมต่อฐานข้อมูล MongoDB
    $db = new Database();
    $cart = $db->getCollection('cart');

    $email = $_GET['email'] ?? '';
    if (!$email) {
        echo json_encode(["status" => "error", "message" => "ต้องระบุ email"]);
        exit;
    }

    // ✅ Aggregate Pipeline รองรับ product_id ทั้ง ObjectId และ String
    $pipeline = [
        ['$match' => ['email' => $email]],
        [
            '$addFields' => [
                'product_id_str' => ['$toString' => '$product_id']
            ]
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'let' => ['pid' => '$product_id_str'],
                'pipeline' => [
                    [
                        '$addFields' => [
                            'pid_str' => ['$toString' => '$_id']
                        ]
                    ],
                    [
                        '$match' => [
                            '$expr' => ['$eq' => ['$pid_str', '$$pid']]
                        ]
                    ]
                ],
                'as' => 'product_info'
            ]
        ],
        [
            '$unwind' => [
                'path' => '$product_info',
                'preserveNullAndEmptyArrays' => true
            ]
        ],
        [
            '$project' => [
                '_id' => ['$toString' => '$_id'],
                'product_id' => ['$toString' => '$product_id'],

                // ✅ ใช้ชื่อฟิลด์จริงจาก products.name
                'product_name' => [
                    '$ifNull' => ['$product_info.name', '$product_name']
                ],

                // ✅ ใช้ราคาจาก products.price ถ้าใน cart ไม่มี
                'price' => [
                    '$ifNull' => ['$product_info.price', '$price']
                ],

                // ✅ ปริมาณจาก cart
                'qty' => 1,

                // ✅ stock ใช้ค่าจาก products.stock ถ้าไม่มีให้เป็น 9999 (กัน null)
                'stock' => [
                    '$ifNull' => ['$product_info.stock', 9999]
                ],

                // ✅ รูปภาพจาก products.image
                'image' => [
                    '$ifNull' => ['$product_info.image', '']
                ]
            ]
        ]
    ];

    $result = $cart->aggregate($pipeline);
    $data = iterator_to_array($result);

    echo json_encode([
        "status" => "success",
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
