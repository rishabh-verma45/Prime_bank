<?php
// File: php/check_pin.php - DEBUG PIN ISSUE
require_once 'config.php';

session_start();

echo "<h2>PIN Debug Information</h2>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red'>❌ You are not logged in. Please login first.</p>";
    echo '<a href="/primebank/">Go to Login</a>';
    exit;
}

$user_id = $_SESSION['user_id'];

$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, name, email, txn_pin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

echo "<p><strong>User:</strong> " . $user['name'] . " (" . $user['email'] . ")</p>";
echo "<p><strong>Stored PIN Hash:</strong> " . ($user['txn_pin'] ? $user['txn_pin'] : '<span style="color: red">NULL - NOT SET!</span>') . "</p>";

if (empty($user['txn_pin'])) {
    echo "<p style='color: red'>❌ NO TRANSACTION PIN SET!</p>";
    echo "<p>Please set your PIN using the form below:</p>";
    
    // Show form to set PIN
    echo '
    <form method="POST" action="">
        <label>Enter New PIN (4-6 digits):</label>
        <input type="password" name="new_pin" maxlength="6" required>
        <button type="submit">Set PIN</button>
    </form>
    ';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pin'])) {
        $newPin = $_POST['new_pin'];
        if (preg_match('/^\d{4,6}$/', $newPin)) {
            $hashedPin = password_hash($newPin, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
            if ($stmt->execute([$hashedPin, $user_id])) {
                echo "<p style='color: green'>✅ PIN set successfully! New hash: " . $hashedPin . "</p>";
                echo "<p>Please try sending money again with PIN: <strong>" . $newPin . "</strong></p>";
            } else {
                echo "<p style='color: red'>❌ Failed to set PIN</p>";
            }
        } else {
            echo "<p style='color: red'>❌ PIN must be 4-6 digits only</p>";
        }
    }
    exit;
}

// Test different PIN combinations
echo "<h3>Test PIN Verification:</h3>";
$testPins = ['1234', '0000', '1111', '9999'];

foreach ($testPins as $testPin) {
    $result = password_verify($testPin, $user['txn_pin']);
    echo "<p>PIN: <strong>{$testPin}</strong> - " . ($result ? "<span style='color: green'>✅ VALID</span>" : "<span style='color: red'>❌ Invalid</span>") . "</p>";
}

echo "<h3>Set a New PIN:</h3>";
echo '
<form method="POST" action="">
    <label>Enter New PIN (4-6 digits):</label>
    <input type="password" name="new_pin" maxlength="6" required>
    <button type="submit">Update PIN</button>
</form>
';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pin'])) {
    $newPin = $_POST['new_pin'];
    if (preg_match('/^\d{4,6}$/', $newPin)) {
        $hashedPin = password_hash($newPin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET txn_pin = ? WHERE id = ?");
        if ($stmt->execute([$hashedPin, $user_id])) {
            echo "<p style='color: green'>✅ PIN updated successfully!</p>";
            echo "<p>Your new PIN is: <strong>{$newPin}</strong></p>";
            echo "<p>New hash: {$hashedPin}</p>";
        } else {
            echo "<p style='color: red'>❌ Failed to update PIN</p>";
        }
    } else {
        echo "<p style='color: red'>❌ PIN must be 4-6 digits only</p>";
    }
}
?>