<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("SELECT profile_image FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $oldImage = $stmt->fetchColumn();

        if ($oldImage && str_starts_with($oldImage, '/Blog/uploads/profiles/')) {
            $oldPath = __DIR__ . '/..' . str_replace('/Blog', '', $oldImage);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $stmt = $pdo->prepare("UPDATE user SET profile_image = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        $_SESSION['success'] = "Profile picture removed successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to remove profile picture.";
    }
}

header('Location: /Blog/profile-settings.php');
exit;
