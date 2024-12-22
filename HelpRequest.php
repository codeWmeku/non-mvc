<?php
session_start();

require_once 'Database.php';
$db = new Database();
$pdo = $db->connect();

$userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Guest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }

    $userId = $_SESSION['user']['id'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO help_requests (user_id, category, description, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
    $stmt->execute([$userId, $category, $description]);

    header('Location: HelpRequest.php?success=true');
    exit();
}

$stmt = $pdo->prepare("SELECT help_requests.*, users.name AS user_name FROM help_requests JOIN users ON help_requests.user_id = users.id ORDER BY created_at DESC");
$stmt->execute();
$helpRequests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <title>Help Requests</title>
    <style>
        /* Paste your existing CSS styles here */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f4f4f4; 
            color: #333; 
            font-family: 'Arial', sans-serif;
        }

        nav {
    width: 100%;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: fixed;
    z-index: 1000;
    top: 0;
    padding: 0 20px; 
}

.nav-left {
    display: flex;
    align-items: center;
}

.nav-left .left {
    height: 40px;
    background: #e0e0e0; 
    border-radius: 20px;
    padding: 0 12px;
    display: flex;
    align-items: center;
}

.nav-center {
    display: flex;
    align-items: center;
}

.nav-center a {
    margin-left: 20px; 
    text-decoration: none;
}

.nav-center i {
    background: none;
    border-radius: 10px;
    font-size: 24px; 
    padding: 15px 25px; 
    color: #646262;
    transition: background 0.3s, color 0.3s;
}

.nav-center a:hover i {
    background: #e0e0e0; 
    color: #252525;
}

.nav-right {
    display: flex;
    align-items: center;
}

.nav-right a {
    margin-left: 12px;
}

.nav-right i {
    background: #e0e0e0; 
    height: 40px;
    width: 40px;
    border-radius: 50%;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.nav-right a:hover i {
    background: #b9b7b7; 
}

.nav-toggle {
    display: none; 
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    margin-left: 20px; 
}

@media (max-width: 768px) {
    .nav-center {
        display: none; 
        flex-direction: column; 
        position: absolute; 
        top: 60px; 
        left: 0;
        right: 0;
        background: #ffffff;
    
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 999; 
    }

    .nav-center.active {
        display: flex; 
    }

    .nav-toggle {
        display: block; 
    }
}

@media (min-width: 769px) {
    .nav-toggle {
        display: none; 
    }
}

       /* Additional Styles for Main Content */
.main-content {
    display: flex;
    justify-content: space-between;
    margin-top: 60px; /* Adjust the margin to match the height of the nav bar */
    padding: 20px;
    gap: 20px; /* Added gap for spacing between left and right columns */
}

.main-left {
    width: 100%; /* Use the full width of the container */
    padding: 20px;
    background: #ffffff; /* White background */
    border-radius: 8px; /* Border radius for a soft look */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Styling for Form */
form {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

form label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

form input, form select, form textarea {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
}

form input[type="submit"] {
    background: #007bff; /* Bootstrap primary color */
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

form input[type="submit"]:hover {
    background: #0056b3;
}

/* Styling for Help Requests Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 12px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .main-content {
        flex-direction: column;
    }

    .main-left {
        width: 100%;
    }
}

    </style>
</head>
<body>

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
<div class="main-content">
    <div class="main-left">
        <h1>Help Requests</h1>
        <form action="HelpRequest.php" method="POST">
            <?php if (isset($_SESSION['user'])): ?>
                <p><strong>User:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?> (ID: <?= htmlspecialchars($_SESSION['user']['id']) ?>)</p>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user']['id']) ?>">
            <?php else: ?>
                <p>You must be logged in to submit a help request.</p>
                <a href="login.php">Login</a>
                <?php exit; ?>
            <?php endif; ?>

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="" disabled selected>Select a category</option>
                <option value="Education">Education</option>
                <option value="Health">Health</option>
                <option value="Tech Support">Tech Support</option>
                <option value="Other">Other</option>
            </select>
            <br>

            <label for="description">Description:</label><br>
            <textarea id="description" name="description" rows="4" cols="50" required></textarea>
            <br>

            <input type="submit" value="Submit Request">
        </form>

        <h2>Recent Help Requests</h2>
        <?php if (!empty($helpRequests)): ?>
            <table border="1">
                <tr>
                    <th>User</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
                <?php foreach ($helpRequests as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['user_name']) ?></td>
                        <td><?= htmlspecialchars($request['category']) ?></td>
                        <td><?= htmlspecialchars($request['description']) ?></td>
                        <td><?= htmlspecialchars($request['status']) ?></td>
                        <td><?= htmlspecialchars($request['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No help requests found.</p>
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
