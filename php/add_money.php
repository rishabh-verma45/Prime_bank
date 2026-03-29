<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Please login first');
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['amount'])) {
    sendResponse(false, 'Amount required');
}

$amount = floatval($data['amount']);

if ($amount <= 0 || $amount > 100000) {
    sendResponse(false, 'Invalid amount (₹1 - ₹100,000)');
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT balance, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$newBalance = $user['balance'] + $amount;

$stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
if ($stmt->execute([$newBalance, $userId])) {
    addTransaction($userId, 'Credit', $amount, $newBalance, "Money added to account");
    
    $subject = "Money Added - PrimeBank";
    $body = "<h2>Money Added</h2><p>Amount added: ₹" . number_format($amount, 2) . "</p><p>New balance: ₹" . number_format($newBalance, 2) . "</p>";
    sendEmail($user['email'], $subject, $body);
    
    sendResponse(true, "₹" . number_format($amount, 2) . " added successfully", ['new_balance' => $newBalance]);
} else {
    sendResponse(false, 'Failed to add money');
}
?>