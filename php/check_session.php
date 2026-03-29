<?php
require_once 'config.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    sendResponse(true, 'Session active', [
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ]
    ]);
} else {
    sendResponse(true, 'No active session', ['logged_in' => false]);
}
?>