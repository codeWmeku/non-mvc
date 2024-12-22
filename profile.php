<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php'); 
    exit();
}

require_once 'Database.php';

$db = new Database();
$pdo = $db->connect();

$userId = $_SESSION['user']['id'];

$userProfile = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching user profile: " . $e->getMessage());
    echo "An error occurred while fetching your profile.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" 
          integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./css/profile.css">
</head>
<body>
<div class="container">
    <header>
        <h1>User Profile</h1>
    </header>

    <div class="profile-info">
        <div class="profile-card">
            <h2>Profile Details</h2>
            <?php if ($userProfile): ?>
                <p><span>Name:</span> <?= htmlspecialchars($userProfile['name']) ?></p>
                <p><span>Email:</span> <?= htmlspecialchars($userProfile['email']) ?></p>
                <p><span>Role:</span> <?= htmlspecialchars($userProfile['role']) ?></p>
                <p><span>Status:</span> <?= htmlspecialchars($userProfile['status']) ?></p>
                <p><span>Joined on:</span> <?= htmlspecialchars($userProfile['created_at']) ?></p>
            <?php else: ?>
                <p>User profile not found.</p>
            <?php endif; ?>
            <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Bayanihub@gmail.com</p>
    </footer>
</div>
</body>
</html>
