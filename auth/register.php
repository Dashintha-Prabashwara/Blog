<?php
// Start session at the very beginning
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $return_url = $_POST['return'] ?? '/Blog/dashboard.php';

    // Validation
    $errors = [];
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: /Blog/register.php');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email already registered";
            header('Location: /Blog/register.php');
            exit;
        }

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$username, $email, $hash]);

        // Auto-login after registration
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;

        // Redirect to return URL if set
        header('Location: ' . $return_url);
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: /Blog/register.php' . ($return_url ? '?return=' . urlencode($return_url) : ''));
        exit;
    }
}

// If we get here, redirect back to register page
header('Location: /Blog/register.php');
exit;
