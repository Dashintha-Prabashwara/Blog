<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to delete comments']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? 0;

try {
    // Check if user owns the comment or is the post author
    $stmt = $pdo->prepare("
        SELECT c.user_id, p.user_id as post_author_id 
        FROM comment c
        JOIN blogPost p ON c.post_id = p.id
        WHERE c.id = ?
    ");
    $stmt->execute([$commentId]);
    $result = $stmt->fetch();

    if (!$result || ($result['user_id'] !== $_SESSION['user_id'] && $result['post_author_id'] !== $_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Delete comment
    $stmt = $pdo->prepare("DELETE FROM comment WHERE id = ?");
    $stmt->execute([$commentId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
