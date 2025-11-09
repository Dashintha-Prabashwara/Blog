<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';  // Add this line

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to like posts']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? 0;

try {
    // Check if already liked
    $stmt = $pdo->prepare("SELECT 1 FROM post_like WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM post_like WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO post_like (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        $liked = true;

        // Get post author and create notification
        $stmt = $pdo->prepare("SELECT user_id FROM blogPost WHERE id = ?");
        $stmt->execute([$postId]);
        $authorId = $stmt->fetchColumn();

        // Create notification
        createNotification($authorId, 'like', $postId);
    }

    // Get updated like count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_like WHERE post_id = ?");
    $stmt->execute([$postId]);
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
