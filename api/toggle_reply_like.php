<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to like replies']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$replyId = $data['reply_id'] ?? 0;

try {
    // Check if already liked
    $stmt = $pdo->prepare("SELECT 1 FROM reply_like WHERE reply_id = ? AND user_id = ?");
    $stmt->execute([$replyId, $_SESSION['user_id']]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM reply_like WHERE reply_id = ? AND user_id = ?");
        $stmt->execute([$replyId, $_SESSION['user_id']]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO reply_like (reply_id, user_id) VALUES (?, ?)");
        $stmt->execute([$replyId, $_SESSION['user_id']]);
        $liked = true;
    }

    echo json_encode(['success' => true, 'liked' => $liked]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
