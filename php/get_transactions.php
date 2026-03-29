<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Not authenticated');
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT id, type, amount, balance_after, description, reference_id,
           DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as date 
    FROM transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

sendResponse(true, 'Transactions retrieved', $transactions);
?>