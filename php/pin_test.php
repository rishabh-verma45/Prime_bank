<?php
// File: php/pin_test.php - TEST PIN SYSTEM
require_once 'config.php';
session_start();

echo "<h1>PIN System Test</h1>";

// Auto login demo user
if (!isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = 'demo@primebank.com'");
    $stmt->execute();
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo "<p>Logged in as: " . $user['name'] . "</p>";
    }
}

$userId = $_SESSION['user_id'];
$pdo = getDB();

// Get current user
$stmt = $pdo->prepare("SELECT name, email, balance, txn_pin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "<h2>Current Status</h2>";
echo "Name: " . $user['name'] . "<br>";
echo "PIN stored: " . ($user['txn_pin'] ? $user['txn_pin'] : 'NOT SET') . "<br>";

// Set PIN form
echo "<h2>Set PIN</h2>";
echo '<form method="POST">
        <input type="password" name="pin" placeholder="Enter 4-6 digit PIN" required>
        <button type="submit" name="action" value="set">Set PIN</button>
      </form>';

if (isset($_POST['action']) && $_POST['action'] === 'set') {
    $pin = $_POST['pin'];
    if (preg_match('/^\d{4,6}$/', $pin)) {
        $hashed = md5($pin);
        $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $userId])) {
            echo "<p style='color:green'>✅ PIN set to: {$pin}</p>";
            echo "<p>MD5 Hash: {$hashed}</p>";
        }
    } else {
        echo "<p style='color:red'>Invalid PIN format</p>";
    }
}

// Test PIN form
echo "<h2>Test PIN</h2>";
echo '<form method="POST">
        <input type="password" name="test_pin" placeholder="Enter PIN to test" required>
        <button type="submit" name="action" value="test">Test PIN</button>
      </form>';

if (isset($_POST['action']) && $_POST['action'] === 'test') {
    $testPin = $_POST['test_pin'];
    $hashedTest = md5($testPin);
    $storedPin = $user['txn_pin'];
    
    echo "<p>Entered PIN: {$testPin}</p>";
    echo "<p>MD5 of entered: {$hashedTest}</p>";
    echo "<p>Stored PIN hash: {$storedPin}</p>";
    
    if ($hashedTest === $storedPin) {
        echo "<p style='color:green; font-size:20px'>✅ PIN IS VALID!</p>";
    } else {
        echo "<p style='color:red; font-size:20px'>❌ PIN IS INVALID!</p>";
    }
}

// Set default PIN button
echo "<h2>Quick Actions</h2>";
echo '<form method="POST">
        <button type="submit" name="action" value="set1234">Set PIN to 1234</button>
      </form>';

if (isset($_POST['action']) && $_POST['action'] === 'set1234') {
    $hashed = md5('1234');
    $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
    if ($stmt->execute([$hashed, $userId])) {
        echo "<p style='color:green'>✅ PIN set to 1234</p>";
        echo "<p>Hash: {$hashed}</p>";
    }
}
?>