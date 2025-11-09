<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';  // Add this line

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to like comments']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? 0;

try {
    // Begin transaction
    $pdo->beginTransaction();

    // First, remove any existing dislike
    $stmt = $pdo->prepare("DELETE FROM comment_dislike WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);

    // Check if already liked
    $stmt = $pdo->prepare("SELECT 1 FROM comment_like WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM comment_like WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO comment_like (comment_id, user_id) VALUES (?, ?)");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        $liked = true;

        // Get comment author and create notification
        $stmt = $pdo->prepare("SELECT user_id FROM comment WHERE id = ?");
        $stmt->execute([$commentId]);
        $authorId = $stmt->fetchColumn();
        
        // Create notification
        createNotification($authorId, 'comment_like', null, $commentId);
    }

    // Get updated counts
    $likeCount = getCommentLikeCount($commentId);
    $dislikeCount = getCommentDislikeCount($commentId);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'count' => $likeCount,
        'dislikeCount' => $dislikeCount
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
