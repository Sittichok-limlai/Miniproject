<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
  $db = new Database();
  $coupons = $db->getCollection('coupons');

  $data = json_decode(file_get_contents('php://input'), true);
  $id = $data['_id'] ?? '';

  if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีรหัสคูปอง']);
    exit;
  }

  $coupons->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
  echo json_encode(['status' => 'success', 'message' => 'ลบคูปองสำเร็จ']);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
