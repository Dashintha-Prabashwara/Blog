<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    $return = $_POST['return'] ?? '/Blog/dashboard.php';

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['success'] = 'Successfully logged in!';
            header('Location: ' . $return);
            exit;
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header('Location: /Blog/login.php' . ($return ? '?return=' . urlencode($return) : ''));
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header('Location: /Blog/login.php' . ($return ? '?return=' . urlencode($return) : ''));
        exit;
    }
}

header('Location: /Blog/login.php');
exit;
