<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Blog/login.php');
    exit;
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (in_array($file_extension, $allowed_extensions)) {
        // Fetch old image to delete it
        $stmt = $pdo->prepare("SELECT profile_image FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $oldImage = $stmt->fetchColumn();

        $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $image_url = '/Blog/uploads/profiles/' . $new_filename;
            
            $stmt = $pdo->prepare("UPDATE user SET profile_image = ? WHERE id = ?");
            $stmt->execute([$image_url, $_SESSION['user_id']]);
            
            // Delete old file if present
            if ($oldImage && str_starts_with($oldImage, '/Blog/uploads/profiles/')) {
                $oldPath = __DIR__ . '/..' . str_replace('/Blog', '', $oldImage);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $_SESSION['success'] = "Profile picture updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to upload image.";
        }
    } else {
        $_SESSION['error'] = "Invalid file type. Allowed formats: JPG, PNG, WEBP, GIF.";
    }
}

header('Location: /Blog/profile-settings.php');
exit;
