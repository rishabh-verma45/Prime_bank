<?php
// File: php/verify_otp.php - Debug endpoint
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['otp']) || !isset($data['purpose'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$email = trim($data['email']);
$otp = trim($data['otp']);
$purpose = $data['purpose'];

$pdo = getDB();
$now = date('Y-m-d H:i:s');

// Check OTP
$stmt = $pdo->prepare("
    SELECT id, expires_at, is_used FROM otp_codes 
    WHERE email = ? AND otp = ? AND purpose = ? 
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$email, $otp, $purpose]);
$record = $stmt->fetch();

if (!$record) {
    echo json_encode(['success' => false, 'message' => 'OTP not found in database']);
    exit;
}

if ($record['is_used']) {
    echo json_encode(['success' => false, 'message' => 'OTP has already been used']);
    exit;
}

if ($now > $record['expires_at']) {
    echo json_encode([
        'success' => false, 
        'message' => 'OTP has expired. Expired at: ' . $record['expires_at'] . ' | Current time: ' . $now
    ]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'OTP is valid']);
?>