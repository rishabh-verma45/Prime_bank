<?php
// File: php/send_money.php - USING MD5 VERIFICATION
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['recipient']) || !isset($data['amount']) || !isset($data['txn_pin']) || !isset($data['method'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipientId = trim($data['recipient']);
$amount = floatval($data['amount']);
$enteredPin = trim($data['txn_pin']);
$method = $data['method'];

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

$pdo = getDB();

// Get sender with PIN
$stmt = $pdo->prepare("SELECT id, name, email, balance, txn_pin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$sender = $stmt->fetch();

if (!$sender) {
    echo json_encode(['success' => false, 'message' => 'Sender not found']);
    exit;
}

// Check if PIN is set
if (empty($sender['txn_pin'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction PIN not set. Please go to Reset PIN page and set your PIN first.']);
    exit;
}

// Verify PIN using MD5
$hashedEnteredPin = md5($enteredPin);
$pinValid = ($hashedEnteredPin === $sender['txn_pin']);

if (!$pinValid) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction PIN. Please try again.']);
    exit;
}

// Check balance
if ($sender['balance'] < $amount) {
    echo json_encode(['success' => false, 'message' => 'Insufficient balance. Available: ₹' . number_format($sender['balance'], 2)]);
    exit;
}

// Find recipient
$recipient = null;
if ($method === 'account') {
    $stmt = $pdo->prepare("SELECT id, name, email, balance FROM users WHERE account_number = ?");
    $stmt->execute([$recipientId]);
    $recipient = $stmt->fetch();
} elseif ($method === 'phone') {
    $stmt = $pdo->prepare("SELECT id, name, email, balance FROM users WHERE phone = ?");
    $stmt->execute([$recipientId]);
    $recipient = $stmt->fetch();
} elseif ($method === 'upi') {
    $stmt = $pdo->prepare("SELECT id, name, email, balance FROM users WHERE upi_id = ?");
    $stmt->execute([$recipientId]);
    $recipient = $stmt->fetch();
}

if (!$recipient) {
    echo json_encode(['success' => false, 'message' => 'Recipient not found']);
    exit;
}

if ($recipient['id'] == $sender['id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot send money to yourself']);
    exit;
}

// Process transaction
try {
    $pdo->beginTransaction();
    
    $newSenderBalance = $sender['balance'] - $amount;
    $newRecipientBalance = $recipient['balance'] + $amount;
    
    $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->execute([$newSenderBalance, $sender['id']]);
    $stmt->execute([$newRecipientBalance, $recipient['id']]);
    
    $referenceId = 'TXN' . date('Ymd') . rand(1000, 9999);
    
    // Record sender transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'Debit', ?, ?, ?, ?)");
    $stmt->execute([$sender['id'], $amount, $newSenderBalance, "Sent to {$recipient['name']}", $referenceId]);
    
    // Record recipient transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'Credit', ?, ?, ?, ?)");
    $stmt->execute([$recipient['id'], $amount, $newRecipientBalance, "Received from {$sender['name']}", $referenceId]);
    
    $pdo->commit();
    
    // Send email notifications
    try {
        $senderSubject = "Money Sent - PrimeBank";
        $senderBody = "<h2>Money Sent</h2><p>You sent ₹" . number_format($amount, 2) . " to {$recipient['name']}</p><p>Reference: {$referenceId}</p>";
        sendEmail($sender['email'], $senderSubject, $senderBody);
        
        $recipientSubject = "Money Received - PrimeBank";
        $recipientBody = "<h2>Money Received</h2><p>You received ₹" . number_format($amount, 2) . " from {$sender['name']}</p><p>Reference: {$referenceId}</p>";
        sendEmail($recipient['email'], $recipientSubject, $recipientBody);
    } catch (Exception $e) {}
    
    echo json_encode(['success' => true, 'message' => "₹" . number_format($amount, 2) . " sent successfully to {$recipient['name']}"]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>