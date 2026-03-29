<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$otp = isset($data['otp']) ? trim($data['otp']) : '';

$pdo = getDB();

$stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// DEMO ACCOUNT - NO OTP
if ($email === 'demo@primebank.com') {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    echo json_encode(['success' => true, 'message' => 'Login successful', 'data' => ['name' => $user['name'], 'email' => $user['email']]]);
    exit;
}

// IF OTP PROVIDED, VERIFY IT
if (!empty($otp)) {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id FROM otp_codes WHERE email = ? AND otp = ? AND purpose = 'login' AND expires_at > ? AND is_used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email, $otp, $now]);
    $otpRecord = $stmt->fetch();
    
    if (!$otpRecord) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
    $stmt->execute([$otpRecord['id']]);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    
    echo json_encode(['success' => true, 'message' => 'Login successful', 'data' => ['name' => $user['name'], 'email' => $user['email']]]);
    exit;
}

// NO OTP PROVIDED - SEND OTP
$stmt = $pdo->prepare("DELETE FROM otp_codes WHERE email = ? AND purpose = 'login' AND is_used = 0");
$stmt->execute([$email]);

$otpCode = sprintf("%06d", mt_rand(1, 999999));
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare("INSERT INTO otp_codes (email, otp, purpose, expires_at) VALUES (?, ?, 'login', ?)");
$stmt->execute([$email, $otpCode, $expires_at]);

$subject = "PrimeBank - Login OTP";
$body = "<h2>Your Login OTP: <strong style='font-size:24px'>$otpCode</strong></h2><p>Valid for 10 minutes.</p>";
sendEmail($email, $subject, $body);

echo json_encode(['success' => false, 'require_otp' => true, 'message' => 'OTP sent to your email']);
?>