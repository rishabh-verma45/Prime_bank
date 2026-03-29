<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'primebank');

// Email Configuration (Update with your Gmail details)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'techibudy@gmail.com');  // REPLACE WITH YOUR EMAIL
define('SMTP_PASS', 'kbmhhzkhkwtpnyyl');     // REPLACE WITH GMAIL APP PASSWORD
define('SMTP_FROM', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'PrimeBank');

// Database Connection
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
}

// Send Email
function sendEmail($to, $subject, $body) {
    require_once '../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $e->getMessage());
        return false;
    }
}

// Generate OTP
function generateOTP() {
    return sprintf("%06d", mt_rand(1, 999999));
}

// Save OTP
function saveOTP($email, $otp, $purpose) {
    $pdo = getDB();
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Delete old OTPs
    $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE email = ? AND purpose = ? AND is_used = 0");
    $stmt->execute([$email, $purpose]);
    
    // Insert new OTP
    $stmt = $pdo->prepare("INSERT INTO otp_codes (email, otp, purpose, expires_at) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$email, $otp, $purpose, $expires_at]);
}

// Verify OTP
function verifyOTP($email, $otp, $purpose) {
    $pdo = getDB();
    $now = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        SELECT id FROM otp_codes 
        WHERE email = ? AND otp = ? AND purpose = ? 
        AND expires_at > ? AND is_used = 0 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$email, $otp, $purpose, $now]);
    $result = $stmt->fetch();
    
    if ($result) {
        $stmt = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
        $stmt->execute([$result['id']]);
        return true;
    }
    return false;
}

// Add Transaction
function addTransaction($user_id, $type, $amount, $balance_after, $description, $reference_id = null) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, type, amount, balance_after, description, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$user_id, $type, $amount, $balance_after, $description, $reference_id]);
}

// Generate Account Number
function generateAccountNumber() {
    return 'PB' . date('Ymd') . rand(1000, 9999);
}

// Generate UPI ID
function generateUPIId($name) {
    $clean = preg_replace('/[^a-z0-9]/i', '', strtolower($name));
    return $clean . rand(100, 999) . '@prime';
}

// Send Response
function sendResponse($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}
?>