<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to dislike comments']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? 0;

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Remove like if exists
    $stmt = $pdo->prepare("DELETE FROM comment_like WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);

    // Check if already disliked
    $stmt = $pdo->prepare("SELECT 1 FROM comment_dislike WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Remove dislike
        $stmt = $pdo->prepare("DELETE FROM comment_dislike WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        $disliked = false;
    } else {
        // Add dislike
        $stmt = $pdo->prepare("INSERT INTO comment_dislike (comment_id, user_id) VALUES (?, ?)");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        $disliked = true;
    }

    // Get updated counts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_dislike WHERE comment_id = ?");
    $stmt->execute([$commentId]);
    $dislikeCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_like WHERE comment_id = ?");
    $stmt->execute([$commentId]);
    $likeCount = $stmt->fetchColumn();

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'disliked' => $disliked,
        'count' => $dislikeCount,
        'likeCount' => $likeCount
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
