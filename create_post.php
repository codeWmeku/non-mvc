<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postText = $_POST['post_text'] ?? '';
    $postType = $_POST['post_type'] ?? '';

    if (!empty($postText)) {
        require_once 'Database.php';
        $db = new Database();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("INSERT INTO posts (post_text, post_type, created_at, user_id) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([
            htmlspecialchars($postText),
            htmlspecialchars($postType),
            $_SESSION['user']['id'], 
        ]);

        header('Location: dashboard.php?success=true');
        exit();
    }
}

header('Location: dashboard.php');
exit();
?>
