<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$topics = $data['topics'] ?? '';
$draftId = $data['draft_id'] ?? null;

try {
    if ($draftId) {
        $stmt = $pdo->prepare("UPDATE draft_post SET title = ?, content = ?, topics = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $topics, $draftId, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO draft_post (user_id, title, content, topics) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $topics]);
        $draftId = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'draft_id' => $draftId,
        'message' => 'Draft saved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save draft']);
}
