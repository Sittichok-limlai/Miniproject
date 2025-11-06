<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header('Content-Type: application/json');

try {
  $db = new Database();
  $coupons = $db->getCollection('coupons');
  $list = $coupons->find(['is_active' => true]);

  $result = [];
  foreach ($list as $c) {
    $c['_id'] = (string)$c['_id'];
    $result[] = $c;
  }

  echo json_encode(['status' => 'success', 'data' => $result]);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
