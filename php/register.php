<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['password']) || !isset($data['otp'])) {
    sendResponse(false, 'All fields are required');
}

$name = trim($data['name']);
$email = trim($data['email']);
$phone = trim($data['phone']);
$password = $data['password'];
$otp = trim($data['otp']);

if (strlen($password) < 4) {
    sendResponse(false, 'Password must be at least 4 characters');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email format');
}

$pdo = getDB();

// Check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendResponse(false, 'Email already registered');
}

// Verify OTP
if (!verifyOTP($email, $otp, 'register')) {
    sendResponse(false, 'Invalid or expired OTP');
}

// Create user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$accountNumber = generateAccountNumber();
$upiId = generateUPIId($name);

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, phone, password, account_number, upi_id, balance) 
    VALUES (?, ?, ?, ?, ?, ?, 5000)
");

if ($stmt->execute([$name, $email, $phone, $hashedPassword, $accountNumber, $upiId])) {
    $userId = $pdo->lastInsertId();
    addTransaction($userId, 'Credit', 5000, 5000, 'Welcome Bonus');
    
    // Send welcome email
    $subject = "Welcome to PrimeBank!";
    $body = "
        <div style='font-family: Arial, sans-serif;'>
            <h2>Welcome to PrimeBank!</h2>
            <p>Dear {$name},</p>
            <p>Your account has been successfully created.</p>
            <p><strong>Account Number:</strong> {$accountNumber}</p>
            <p><strong>UPI ID:</strong> {$upiId}</p>
            <p>You have received ₹5,000 as a welcome bonus.</p>
        </div>
    ";
    sendEmail($email, $subject, $body);
    
    sendResponse(true, 'Registration successful! Please login.');
} else {
    sendResponse(false, 'Registration failed');
}
?>