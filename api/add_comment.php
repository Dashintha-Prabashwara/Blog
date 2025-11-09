<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';  // Add this line

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to comment']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? 0;
$content = trim($data['content'] ?? '');

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comment (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $_SESSION['user_id'], $content]);
    $commentId = $pdo->lastInsertId();

    // Get post author and create notification
    $stmt = $pdo->prepare("SELECT user_id FROM blogPost WHERE id = ?");
    $stmt->execute([$postId]);
    $authorId = $stmt->fetchColumn();
    
    // Create notification
    createNotification($authorId, 'comment', $postId, $commentId);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
