<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

$db = new Database();
$products = $db->getCollection('products');

$cursor = $products->find(); // ✅ ดึงทั้งหมด
$data = [];
foreach ($cursor as $doc) {
    $doc['_id'] = (string)$doc['_id'];
    $data[] = $doc;
}

echo json_encode(["status" => "success", "data" => $data]);
