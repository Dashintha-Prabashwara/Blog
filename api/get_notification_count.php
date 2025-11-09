<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id = ? AND `read` = 0");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['count' => (int)$stmt->fetchColumn()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
