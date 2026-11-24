<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';  // Add this line

header('Content-Type: application/json');

$actingUserId = $_SESSION['user_id'] ?? null;
if (!$actingUserId) {
    if (!isset($_SESSION['guest_user_id'])) {
        $sessHash = substr(md5(session_id() . ($_SERVER['REMOTE_ADDR'] ?? '')), 0, 10);
        $guestEmail = 'reader_' . $sessHash . '@guest.local';
        $guestName = 'Reader_' . $sessHash;
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$guestEmail]);
        $guestId = $stmt->fetchColumn();
        if (!$guestId) {
            $stmt = $pdo->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, '', 'guest')");
            $stmt->execute([$guestName, $guestEmail]);
            $guestId = $pdo->lastInsertId();
        }
        $_SESSION['guest_user_id'] = (int)$guestId;
    }
    $actingUserId = $_SESSION['guest_user_id'];
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? 0;

try {
    // Check if already liked
    $stmt = $pdo->prepare("SELECT 1 FROM post_like WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $actingUserId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM post_like WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $actingUserId]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO post_like (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $actingUserId]);
        $liked = true;

        // Get post author and create notification
        $stmt = $pdo->prepare("SELECT user_id FROM blogpost WHERE id = ?");
        $stmt->execute([$postId]);
        $authorId = $stmt->fetchColumn();

        // Create notification
        if ($authorId && (int)$authorId !== (int)$actingUserId) {
            createNotification($authorId, 'like', $postId, $actingUserId);
        }
    }

    // Get updated like count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_like WHERE post_id = ?");
    $stmt->execute([$postId]);
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
