<?php
// File: php/get_user.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, balance, account_number, upi_id, 
           CASE WHEN txn_pin IS NOT NULL AND txn_pin != '' THEN 1 ELSE 0 END as has_txn_pin,
           DATE_FORMAT(created_at, '%Y-%m-%d') as created_at
    FROM users WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(['success' => true, 'message' => 'User data retrieved', 'data' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>