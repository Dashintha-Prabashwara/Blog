<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Post ID is required']);
    exit;
}

$postId = (int)$_GET['post_id'];

try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_image,
               DATE_FORMAT(c.created_at, '%Y-%m-%dT%TZ') as created_at
        FROM comment c 
        JOIN user u ON c.user_id = u.id 
        WHERE c.post_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$postId]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
