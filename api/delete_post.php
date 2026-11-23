<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

try {
    // Check post ownership
    $stmt = $pdo->prepare("SELECT user_id, image_url FROM blogpost WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post || (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Delete post
    $stmt = $pdo->prepare("DELETE FROM blogpost WHERE id = ?");
    $stmt->execute([$id]);

    // Clean up uploaded image if exists
    if (!empty($post['image_url'])) {
        $imgPath = __DIR__ . '/..' . str_replace('/Blog', '', $post['image_url']);
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
