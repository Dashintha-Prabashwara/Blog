<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header('Location: /Blog/dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header('Location: /Blog/login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Login failed. Please try again.";
        header('Location: /Blog/login.php');
        exit;
    }
}

header('Location: /Blog/login.php');
exit;
