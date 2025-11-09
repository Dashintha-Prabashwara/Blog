<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['notifications' => []]);
    exit;
}

try {
    $notifications = getNotifications($_SESSION['user_id']);
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
