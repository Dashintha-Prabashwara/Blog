<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate lengths
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "New password must be at least 8 characters long.";
        header('Location: /Blog/security-settings.php');
        exit;
    }

    // Verify passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header('Location: /Blog/security-settings.php');
        exit;
    }

    // Get current user data
    $stmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();

    // Verify current password
    if (!$hash || !password_verify($current_password, $hash)) {
        $_SESSION['error'] = "Current password is incorrect.";
        header('Location: /Blog/security-settings.php');
        exit;
    }

    // Update password
    $newHash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->execute([$newHash, $_SESSION['user_id']]);

    $_SESSION['success'] = "Password updated successfully!";
}

header('Location: /Blog/security-settings.php');
exit;
