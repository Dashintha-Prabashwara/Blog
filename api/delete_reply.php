<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to delete reply']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$replyId = $data['reply_id'] ?? 0;

try {
    // Verify reply ownership
    $stmt = $pdo->prepare("SELECT user_id FROM comment_reply WHERE id = ?");
    $stmt->execute([$replyId]);
    $reply = $stmt->fetch();

    if (!$reply || $reply['user_id'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM comment_reply WHERE id = ?");
    $stmt->execute([$replyId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
