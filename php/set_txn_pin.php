<?php
// File: php/set_txn_pin.php - SIMPLIFIED VERSION
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['txn_pin'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction PIN required']);
    exit;
}

$txnPin = trim($data['txn_pin']);
$userId = $_SESSION['user_id'];

// Validate PIN format
if (!preg_match('/^\d{4,6}$/', $txnPin)) {
    echo json_encode(['success' => false, 'message' => 'PIN must be 4-6 digits only']);
    exit;
}

try {
    $pdo = getDB();
    
    // Use MD5 for simplicity (change to password_hash later)
    $hashedPin = md5($txnPin);
    
    // Update the database
    $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
    
    if ($stmt->execute([$hashedPin, $userId])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Transaction PIN set successfully! You can now send money.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>