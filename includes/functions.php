<?php

function getLikeCount($postId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_like WHERE post_id = ?");
    $stmt->execute([$postId]);
    return $stmt->fetchColumn();
}

function hasLiked($postId) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post_like WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    return (bool)$stmt->fetchColumn();
}

function isFollowing($userId) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follow WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$_SESSION['user_id'], $userId]);
    return (bool)$stmt->fetchColumn();
}

function getComments($postId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_image 
        FROM comment c 
        JOIN user u ON c.user_id = u.id 
        WHERE c.post_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function hasLikedComment($commentId) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_like WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    return (bool)$stmt->fetchColumn();
}

function hasDislikedComment($commentId) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_dislike WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    return (bool)$stmt->fetchColumn();
}

function getCommentLikeCount($commentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_like WHERE comment_id = ?");
    $stmt->execute([$commentId]);
    return $stmt->fetchColumn();
}

function getCommentDislikeCount($commentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_dislike WHERE comment_id = ?");
    $stmt->execute([$commentId]);
    return $stmt->fetchColumn();
}

function getUserStats($userId) {
    global $pdo;
    
    // Get total likes received
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM post_like pl JOIN blogPost bp ON pl.post_id = bp.id WHERE bp.user_id = ?) as total_likes,
            (SELECT COUNT(*) FROM comment c JOIN blogPost bp ON c.post_id = bp.id WHERE bp.user_id = ?) as total_comments,
            (SELECT COUNT(*) FROM follow WHERE following_id = ?) as followers,
            (SELECT COUNT(*) FROM comment_like cl JOIN comment c ON cl.comment_id = c.id WHERE c.user_id = ?) as comment_likes,
            (SELECT COUNT(*) FROM comment_dislike cd JOIN comment c ON cd.comment_id = c.id WHERE c.user_id = ?) as comment_dislikes
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
    return $stmt->fetch();
}

function getUnreadNotificationCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id = ? AND `read` = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function createNotification($userId, $type, $postId = null, $commentId = null) {
    global $pdo;
    
    // Don't notify yourself
    if ($userId == $_SESSION['user_id']) return;
    
    $stmt = $pdo->prepare("
        INSERT INTO notification (user_id, from_user_id, type, post_id, comment_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $_SESSION['user_id'], $type, $postId, $commentId]);
}

function getNotifications($userId, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT n.*, 
               u.username, u.profile_image,
               bp.title as post_title,
               CASE n.type 
                   WHEN 'comment' THEN ' commented on your post'
                   WHEN 'like' THEN ' liked your post'
                   WHEN 'comment_like' THEN ' liked your comment'
                   WHEN 'comment_dislike' THEN ' disliked your comment'
                   WHEN 'follow' THEN ' started following you'
                   WHEN 'featured' THEN ' Your post was featured!'
               END as action_text,
               TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, n.created_at)) as seconds_ago
        FROM notification n
        JOIN user u ON n.from_user_id = u.id
        LEFT JOIN blogPost bp ON n.post_id = bp.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getTimeAgo($seconds) {
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    
    if ($days > 0) return $days . 'd ago';
    if ($hours > 0) return $hours . 'h ago';
    if ($minutes > 0) return $minutes . 'm ago';
    return 'just now';
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    return true;
}

function showPopup($message, $type = 'info') {
    $colors = [
        'info' => 'bg-blue-50 text-blue-600',
        'error' => 'bg-red-50 text-red-600',
        'success' => 'bg-green-50 text-green-600'
    ];
    $color = $colors[$type] ?? $colors['info'];
    
    echo "
    <div class='fixed top-4 right-4 max-w-md px-6 py-3 {$color} rounded-lg shadow-lg z-50 animate-fade-in-down'>
        <p class='text-sm'>{$message}</p>
    </div>
    <script>
        setTimeout(() => {
            document.querySelector('.animate-fade-in-down').remove();
        }, 3000);
    </script>";
}
