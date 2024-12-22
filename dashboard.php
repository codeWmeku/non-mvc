<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Guest');

require_once 'Database.php';
$db = new Database();
$pdo = $db->connect();

$postsStmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
$postsStmt->execute();
$posts = $postsStmt->fetchAll();

$activitiesStmt = $pdo->prepare("SELECT * FROM activities ORDER BY created_at DESC");
$activitiesStmt->execute();
$recentActivities = $activitiesStmt->fetchAll();

if (isset($_GET['delete_post_id'])) {
    $postId = $_GET['delete_post_id'];

    $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$postId, $_SESSION['user']['id']]);
    
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <link rel="stylesheet" href="./css/dashStyle.css">
    <title>Dashboard</title>
</head>
<body>
<div class="container">
    <nav>
        <div class="nav-left">
            <h1>Welcome, <?= $userName ?></h1>
        </div>
        <button class="nav-toggle" onclick="toggleNav()">☰</button>
        <div class="nav-center">
            <a href="dashboard.php"><i class="fa-solid fa-house"></i></a>
            <a href="HelpRequest.php"><i class="fa-solid fa-tv"></i></a>
            <a href="ResourceSharing.php"><i class="fa-solid fa-share"></i></a>
            <a href="Event.php"><i class="fa-solid fa-calendar"></i></a>
        </div>
        <div class="nav-right">
            <a href="profile.php"><i class="fa-solid fa-user"></i></a>
            <a href="logout.php" onclick="return confirmLogout();"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </nav>

    <div class="main-content">
        <div class="main-left">
            <div class="create-post">
                <form method="POST" action="create_post.php">
                    <div class="ptop">
                        <textarea name="post_text" placeholder="What's your request?" required></textarea>
                        <input type="hidden" name="post_type" value="text"> 
                    </div>
                    <div class="post-button-wrapper">
                        <button type="submit" class="post-button">Post</button>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
                <div class="success-message">Post created successfully!</div>
            <?php endif; ?>

            <div class="posts">
                <h2>Posts</h2>
                <ul>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <li>
                                <h3><?= htmlspecialchars($post['post_text']); ?></h3>
                                <p>Posted by: <?= htmlspecialchars($_SESSION['user']['name']); ?></p>
                                <p>Posted on: <?= htmlspecialchars($post['created_at']); ?></p>
                                <a href="dashboard.php?delete_post_id=<?= $post['id']; ?>" 
                                   class="delete-button" 
                                   onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No posts available.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="main-right">
            <div class="recent-activities">
                <h2>Recent Activities</h2>
                <ul>
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <li>
                                <p><strong><?= htmlspecialchars($activity['activity_type']); ?></strong><br><br>
                                <?= htmlspecialchars($activity['activity_text']); ?> - <br><br>
                                <small><?= htmlspecialchars($activity['created_at']); ?></small></p>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent activities.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleNav() {
        const navCenter = document.querySelector('.nav-center');
        navCenter.classList.toggle('active');
    }

    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>
</body>
</html>
