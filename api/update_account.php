<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);
    $userId = $_SESSION['user_id'];

    try {
        // Check if email is already used by another user
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email already in use by another account.";
            header('Location: /Blog/account-settings.php');
            exit;
        }

        // Update user profile with description
        $stmt = $pdo->prepare("UPDATE user SET username = ?, email = ?, description = ? WHERE id = ?");
        $stmt->execute([$username, $email, $description, $userId]);

        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: /Blog/account-settings.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to update profile. Please try again.";
        header('Location: /Blog/account-settings.php');
        exit;
    }
}

header('Location: /Blog/account-settings.php');
exit;
