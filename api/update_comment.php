<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to update comments']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? 0;
$content = trim($data['content'] ?? '');

try {
    // Verify comment ownership
    $stmt = $pdo->prepare("SELECT user_id FROM comment WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();

    if (!$comment || $comment['user_id'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Update comment
    $stmt = $pdo->prepare("UPDATE comment SET content = ? WHERE id = ?");
    $stmt->execute([$content, $commentId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
