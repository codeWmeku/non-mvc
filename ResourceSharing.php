<?php
session_start();
require_once 'Database.php';
$db = new Database();
$pdo = $db->connect();

$userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Guest');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $availability = htmlspecialchars($_POST['availability']);

    if (isset($_SESSION['user']['id'])) {
        $user_id = $_SESSION['user']['id']; 

        $stmt = $pdo->prepare("INSERT INTO resources (name, category, description, availability, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $availability, $user_id]);

        header('Location: ResourceSharing.php?success=true');
        exit();
    } else {
        echo "You must be logged in to add resources.";
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM resources ORDER BY created_at DESC");
$stmt->execute();
$resources = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Sharing Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <link rel="stylesheet" href="./css/shareStyle.css">
</head>
<body>

<div class="container">

    <!-- Navigation Bar -->
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
            <a href="index.php" onclick="return confirmLogout();"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="content">
        <h1>Resource Sharing Page</h1>
        <form method="POST" class="resource-form">
    <label for="name">Resource Name:</label>
    <input type="text" name="name" id="name" required>

    <label for="category">Category:</label>
    <input type="text" name="category" id="category">

    <label for="description">Description:</label>
    <textarea name="description" id="description"></textarea>

    <label for="availability">Availability:</label>
    <select name="availability" id="availability">
        <option value="available" selected>Available</option>
        <option value="donated">Donated</option>
    </select>

    <button type="submit">Add Resource</button>
</form>

    
<?php if (!empty($resources)): ?>
    <h2>Available Resources</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resources as $resource): ?>
                <tr>
                    <td><?= htmlspecialchars($resource['name']) ?></td>
                    <td><?= htmlspecialchars($resource['category']) ?></td>
                    <td><?= htmlspecialchars($resource['description'] ?? 'No description provided') ?></td>
                    <td><?= htmlspecialchars($resource['availability']) ?></td>
                    <td><?= htmlspecialchars($resource['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No resources available at the moment.</p>
<?php endif; ?>

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