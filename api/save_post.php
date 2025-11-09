<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$topics = $_POST['topics'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Handle image upload
$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_extension, $allowed_extensions)) {
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = '/Blog/uploads/' . $new_filename;
        }
    }
}

try {
    if ($id) {
        $sql = "UPDATE blogPost SET title = ?, content = ?, topics = ?";
        $params = [$title, $content, $topics];
        
        if ($image_url) {
            $sql .= ", image_url = ?";
            $params[] = $image_url;
        }
        
        $sql .= " WHERE id = ? AND user_id = ?";
        $params[] = $id;
        $params[] = $_SESSION['user_id'];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare("INSERT INTO blogPost (user_id, title, content, topics, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $topics, $image_url]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'id' => $id
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
