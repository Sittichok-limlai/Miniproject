<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client;

class Database {
    private $client;
    private $db;

    public function __construct() {
        $uri = "mongodb://127.0.0.1:27017"; // ✅ ใช้ localhost
        $this->client = new Client($uri);
        $this->db = $this->client->mydatabase; // ชื่อ DB ตรงกับใน MongoDB Compass
    }

    public function getCollection($name) {
        return $this->db->$name;
    }
}
