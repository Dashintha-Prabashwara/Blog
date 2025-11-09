<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$draftId = $data['draft_id'] ?? null;

try {
    $stmt = $pdo->prepare("DELETE FROM draft_post WHERE id = ? AND user_id = ?");
    $stmt->execute([$draftId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete draft']);
}
