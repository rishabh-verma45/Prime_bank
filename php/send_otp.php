<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['purpose'])) {
    sendResponse(false, 'Email and purpose required');
}

$email = trim($data['email']);
$purpose = $data['purpose'];

if (!in_array($purpose, ['register', 'login', 'reset'])) {
    sendResponse(false, 'Invalid purpose');
}

$pdo = getDB();

// Get user name if exists
$userName = 'User';
if ($purpose !== 'register') {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        sendResponse(false, 'Email not found');
    }
    $userName = $user['name'];
}

// Generate and save OTP
$otp = generateOTP();
saveOTP($email, $otp, $purpose);

// Send email
$subject = "PrimeBank - " . ucfirst($purpose) . " OTP";
$body = "
    <div style='font-family: Arial, sans-serif;'>
        <h2>PrimeBank Verification</h2>
        <p>Dear {$userName},</p>
        <p>Your OTP for {$purpose} is:</p>
        <div style='background: #f4f4f4; padding: 20px; text-align: center; font-size: 32px; letter-spacing: 5px; font-weight: bold;'>
            {$otp}
        </div>
        <p>This OTP is valid for 10 minutes.</p>
    </div>
";

if (sendEmail($email, $subject, $body)) {
    sendResponse(true, "OTP sent to {$email}");
} else {
    sendResponse(false, "Failed to send OTP. Please try again.");
}
?>