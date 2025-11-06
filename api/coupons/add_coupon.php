<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
  $db = new Database();
  $coupons = $db->getCollection('coupons');

  $data = json_decode(file_get_contents('php://input'), true);

  $newCoupon = [
    'code' => strtoupper(trim($data['code'] ?? '')),
    'discount_type' => $data['discount_type'] ?? 'percent',
    'discount_value' => floatval($data['discount_value'] ?? 0),
    'min_order' => floatval($data['min_order'] ?? 0),
    'expire_date' => $data['expire_date'] ?? null,
    'is_active' => true,
    'created_at' => date('c')
  ];

  if (empty($newCoupon['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุรหัสคูปอง']);
    exit;
  }

  $coupons->insertOne($newCoupon);
  echo json_encode(['status' => 'success', 'message' => 'เพิ่มคูปองสำเร็จ']);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
