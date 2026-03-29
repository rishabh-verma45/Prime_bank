<?php
// File: php/test_pin_direct.php - TEST PIN DIRECTLY
require_once 'config.php';
session_start();

echo "<h1>PIN Test Tool</h1>";

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

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please login first</p>";
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getDB();

// Get user data
$stmt = $pdo->prepare("SELECT name, email, balance, txn_pin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "<h2>Current User</h2>";
echo "Name: " . $user['name'] . "<br>";
echo "Email: " . $user['email'] . "<br>";
echo "Balance: ₹" . $user['balance'] . "<br>";
echo "PIN Hash: " . ($user['txn_pin'] ? substr($user['txn_pin'], 0, 50) . "..." : "NOT SET") . "<br>";

// Form to set PIN
echo "<h2>Set PIN</h2>";
echo '<form method="POST" action="">
        <input type="password" name="new_pin" placeholder="Enter 4-6 digit PIN" required>
        <button type="submit">Set PIN</button>
      </form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pin'])) {
    $newPin = $_POST['new_pin'];
    if (preg_match('/^\d{4,6}$/', $newPin)) {
        $hashed = password_hash($newPin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $userId])) {
            echo "<p style='color:green'>✅ PIN set to: {$newPin}</p>";
            echo "<p>Hash: {$hashed}</p>";
            
            // Test immediately
            $test = password_verify($newPin, $hashed);
            echo "<p>Verification test: " . ($test ? "✅ PASSED" : "❌ FAILED") . "</p>";
        } else {
            echo "<p style='color:red'>Failed to set PIN</p>";
        }
    } else {
        echo "<p style='color:red'>PIN must be 4-6 digits</p>";
    }
}

// Test PIN verification
echo "<h2>Test PIN</h2>";
echo '<form method="POST" action="">
        <input type="password" name="test_pin" placeholder="Enter PIN to test" required>
        <button type="submit">Test PIN</button>
      </form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_pin'])) {
    $testPin = $_POST['test_pin'];
    $result = password_verify($testPin, $user['txn_pin']);
    if ($result) {
        echo "<p style='color:green; font-size:20px'>✅ PIN '{$testPin}' is VALID!</p>";
    } else {
        echo "<p style='color:red; font-size:20px'>❌ PIN '{$testPin}' is INVALID!</p>";
        
        // Try common PINs
        echo "<p>Trying common PINs:</p>";
        $commonPins = ['1234', '0000', '1111', '9999', '123456'];
        foreach ($commonPins as $pin) {
            if (password_verify($pin, $user['txn_pin'])) {
                echo "<p style='color:green'>✅ Found working PIN: {$pin}</p>";
            }
        }
    }
}

// Show all users for reference
echo "<h2>All Users</h2>";
$stmt = $pdo->query("SELECT id, name, email, LEFT(txn_pin, 30) as pin_preview FROM users");
$users = $stmt->fetchAll();
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>PIN Status</th></tr>";
foreach ($users as $u) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['name']}</td>";
    echo "<td>{$u['email']}</td>";
    echo "<td>" . ($u['pin_preview'] ? 'SET' : 'NOT SET') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>