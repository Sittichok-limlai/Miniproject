<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';
header("Content-Type: application/json; charset=UTF-8");

try {
  $db = new Database();
  $collection = $db->getCollection('announcements');

  $docs = $collection->find([], ['sort' => ['created_at' => -1]]);
  $announcements = [];

  foreach ($docs as $d) {
    $announcements[] = [
      "_id" => (string)$d["_id"],
      "title" => $d["title"] ?? "",
      "message" => $d["message"] ?? "",
      "owner_email" => $d["owner_email"] ?? ""
    ];
  }

  echo json_encode(["status" => "success", "announcements" => $announcements]);
} catch (Exception $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
