<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
  $db = new Database();
  $coupons = $db->getCollection('coupons');

  $data = json_decode(file_get_contents('php://input'), true);
  $code = strtoupper(trim($data['code'] ?? ''));
  $orderTotal = floatval($data['order_total'] ?? 0);

  $coupon = $coupons->findOne(['code' => $code, 'is_active' => true]);
  if (!$coupon) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบคูปองนี้']);
    exit;
  }

  // ตรวจวันหมดอายุ
  if (!empty($coupon['expire_date']) && strtotime($coupon['expire_date']) < time()) {
    echo json_encode(['status' => 'error', 'message' => 'คูปองหมดอายุแล้ว']);
    exit;
  }

  // ตรวจยอดขั้นต่ำ
  if ($orderTotal < ($coupon['min_order'] ?? 0)) {
    echo json_encode(['status' => 'error', 'message' => 'ยอดสั่งซื้อน้อยเกินไป']);
    exit;
  }

  $discount = 0;
  if ($coupon['discount_type'] === 'percent') {
    $discount = $orderTotal * ($coupon['discount_value'] / 100);
  } else {
    $discount = $coupon['discount_value'];
  }

  echo json_encode([
    'status' => 'success',
    'message' => 'ใช้คูปองสำเร็จ',
    'discount' => $discount,
    'final_total' => max($orderTotal - $discount, 0)
  ]);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
