<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Please login first');
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['current_password']) || !isset($data['new_password'])) {
    sendResponse(false, 'Current and new password required');
}

$currentPassword = $data['current_password'];
$newPassword = $data['new_password'];

if (strlen($newPassword) < 4) {
    sendResponse(false, 'Password must be at least 4 characters');
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT password, email, name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!password_verify($currentPassword, $user['password'])) {
    sendResponse(false, 'Current password is incorrect');
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
if ($stmt->execute([$hashedPassword, $userId])) {
    $subject = "Password Changed - PrimeBank";
    $body = "<h2>Password Changed</h2><p>Your password has been changed successfully.</p>";
    sendEmail($user['email'], $subject, $body);
    sendResponse(true, 'Password changed successfully');
} else {
    sendResponse(false, 'Failed to change password');
}
?>