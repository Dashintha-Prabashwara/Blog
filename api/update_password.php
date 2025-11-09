<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header('Location: /Blog/profile.php');
        exit;
    }

    // Get current user data
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect.";
        header('Location: /Blog/profile.php');
        exit;
    }

    // Update password
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $_SESSION['user_id']]);

    $_SESSION['success'] = "Password updated successfully!";
}

header('Location: /Blog/profile.php');
exit;
