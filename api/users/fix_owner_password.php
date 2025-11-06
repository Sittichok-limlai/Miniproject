<?php
require_once __DIR__ . '/../config/db.php';

$db = new Database();
$users = $db->getCollection('users');

$email = "owner@gmail.com";
$newPassword = password_hash("123456", PASSWORD_DEFAULT);

$result = $users->updateOne(
    ['email' => $email],
    ['$set' => ['password' => $newPassword]]
);

echo "✅ Updated password hash for owner@gmail.com\n";
?>
