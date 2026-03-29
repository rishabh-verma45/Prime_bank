<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Not authenticated');
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    sendResponse(false, 'No data received');
}

$userId = $_SESSION['user_id'];
$name = trim($data['name']);
$phone = trim($data['phone']);

if (empty($name)) {
    sendResponse(false, 'Name cannot be empty');
}

$pdo = getDB();
$stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");

if ($stmt->execute([$name, $phone, $userId])) {
    sendResponse(true, 'Profile updated successfully');
} else {
    sendResponse(false, 'Failed to update profile');
}
?>