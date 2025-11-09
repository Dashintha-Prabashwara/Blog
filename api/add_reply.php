<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to reply']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? 0;
$parentId = $data['parent_id'] ?? null;
$content = trim($data['content'] ?? '');

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Reply cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comment_reply (comment_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$commentId, $_SESSION['user_id'], $content, $parentId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
