<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

try {
    // First verify password
    $stmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($password, $hash)) {
        echo json_encode(['error' => 'Incorrect password']);
        exit;
    }

    // Start transaction for safe deletion
    $pdo->beginTransaction();

    // Delete all user's data
    $statements = [
        "DELETE FROM post_like WHERE user_id = ?",
        "DELETE FROM comment_like WHERE user_id = ?",
        "DELETE FROM comment_dislike WHERE user_id = ?",
        "DELETE FROM follow WHERE follower_id = ? OR following_id = ?",
        "DELETE FROM notification WHERE user_id = ? OR from_user_id = ?",
        "DELETE FROM draft_post WHERE user_id = ?",
        "DELETE FROM comment WHERE user_id = ?",
        "DELETE FROM blogPost WHERE user_id = ?",
        "DELETE FROM user WHERE id = ?"
    ];

    foreach ($statements as $sql) {
        $stmt = $pdo->prepare($sql);
        if (str_contains($sql, 'OR')) {
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        } else {
            $stmt->execute([$_SESSION['user_id']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete account']);
}
