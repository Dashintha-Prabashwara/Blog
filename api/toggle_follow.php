<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to follow users']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? 0;

try {
    // Check if already following
    $stmt = $pdo->prepare("SELECT 1 FROM follow WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$_SESSION['user_id'], $userId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Unfollow
        $stmt = $pdo->prepare("DELETE FROM follow WHERE follower_id = ? AND following_id = ?");
        $stmt->execute([$_SESSION['user_id'], $userId]);
        $following = false;
    } else {
        // Follow
        $stmt = $pdo->prepare("INSERT INTO follow (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $userId]);
        $following = true;

        // Create notification
        createNotification($userId, 'follow');
    }

    echo json_encode(['success' => true, 'following' => $following]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
