<?php
// File: php/test_pin_verify.php
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Please login first: <a href='/primebank/'>Go to Login</a>";
    exit;
}

$userId = $_SESSION['user_id'];
$testPin = '1234'; // Change this to test different PINs

$pdo = getDB();
$stmt = $pdo->prepare("SELECT txn_pin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "<h2>PIN Verification Test</h2>";
echo "User ID: " . $userId . "<br>";
echo "Stored PIN Hash: " . ($user['txn_pin'] ?? 'NULL') . "<br>";
echo "Testing PIN: " . $testPin . "<br>";

if (empty($user['txn_pin'])) {
    echo "<p style='color: red'>❌ NO PIN SET IN DATABASE!</p>";
} else {
    $result = password_verify($testPin, $user['txn_pin']);
    if ($result) {
        echo "<p style='color: green; font-size: 20px;'>✅ PIN IS VALID! You can send money with PIN: " . $testPin . "</p>";
    } else {
        echo "<p style='color: red; font-size: 20px;'>❌ PIN IS INVALID!</p>";
        echo "<p>Try these common PINs:</p>";
        $commonPins = ['1234', '0000', '1111', '9999', '123456'];
        foreach ($commonPins as $pin) {
            $check = password_verify($pin, $user['txn_pin']);
            if ($check) {
                echo "<p>✅ Found working PIN: <strong>{$pin}</strong></p>";
            }
        }
    }
}

echo "<h3>Reset PIN:</h3>";
echo '<form method="POST" action="">
        <input type="password" name="new_pin" placeholder="Enter new PIN (4-6 digits)">
        <button type="submit">Set New PIN</button>
      </form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pin'])) {
    $newPin = $_POST['new_pin'];
    if (preg_match('/^\d{4,6}$/', $newPin)) {
        $hashedPin = password_hash($newPin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
        if ($stmt->execute([$hashedPin, $userId])) {
            echo "<p style='color: green'>✅ PIN updated to: {$newPin}</p>";
            echo "<p>Hash: {$hashedPin}</p>";
        }
    } else {
        echo "<p style='color: red'>❌ Invalid PIN format</p>";
    }
}
?>