<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$notificationId = $_POST['id'] ?? 'all';

try {
    switch ($action) {
        case 'mark_read':
            if ($notificationId === 'all') {
                $stmt = $pdo->prepare("UPDATE notification SET `read` = 1 WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE notification SET `read` = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$notificationId, $_SESSION['user_id']]);
            }
            break;

        case 'delete':
            if ($notificationId === 'all') {
                $stmt = $pdo->prepare("DELETE FROM notification WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM notification WHERE id = ? AND user_id = ?");
                $stmt->execute([$notificationId, $_SESSION['user_id']]);
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
