<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';  // Add this line

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$postId = (int)($data['post_id'] ?? 0);
$content = trim($data['content'] ?? '');

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit;
}

$commentUserId = $_SESSION['user_id'] ?? null;
if (!$commentUserId) {
    $authorName = trim($data['author_name'] ?? '');
    $authorEmail = trim($data['author_email'] ?? '');
    if (empty($authorName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please enter your name to post a comment']);
        exit;
    }
    if (empty($authorEmail)) {
        $authorEmail = 'reader_' . substr(md5($authorName . session_id()), 0, 10) . '@guest.local';
    }

    // Check if guest user already exists with this email or name
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ? OR username = ?");
    $stmt->execute([$authorEmail, $authorName]);
    $commentUserId = $stmt->fetchColumn();

    if (!$commentUserId) {
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, '', 'guest')");
        $stmt->execute([$authorName, $authorEmail]);
        $commentUserId = $pdo->lastInsertId();
    }
    $_SESSION['guest_user_id'] = (int)$commentUserId;
    $_SESSION['guest_name'] = $authorName;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comment (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $commentUserId, $content]);
    $commentId = $pdo->lastInsertId();

    // Get post author and create notification
    $stmt = $pdo->prepare("SELECT user_id FROM blogpost WHERE id = ?");
    $stmt->execute([$postId]);
    $authorId = $stmt->fetchColumn();
    
    // Create notification
    if ($authorId && (int)$authorId !== (int)$commentUserId) {
        createNotification($authorId, 'comment', $postId, $commentUserId);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
