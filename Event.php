<?php
session_start();
require_once 'Database.php';

$db = new Database();
$pdo = $db->connect();

$userName = htmlspecialchars($_SESSION['user']['name'] ?? 'Guest');

$stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC");
$stmt->execute();
$events = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && $_POST['status'] == 'attending') {
    if (isset($_SESSION['user']['id'])) {  
        $user_id = $_SESSION['user']['id']; 
        $event_id = $_POST['event_id'];

        $rsvpStmt = $pdo->prepare("INSERT INTO events_rsvps (user_id, event_id, status) VALUES (?, ?, ?)");
        $rsvpStmt->execute([$user_id, $event_id, 'attending']);

        $_SESSION['rsvp_message'] = "You have successfully RSVP'd for this event!";
        
        header('Location: Event.php');
        exit();
    } else {
        echo "You must be logged in to RSVP.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <title>Community Events</title>
    <link rel="stylesheet" href="./css/event.css">
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
            <h1>Community Events</h1>

            <?php if (isset($_SESSION['rsvp_message'])): ?>
                <div class="rsvp-notification">
                    <?= htmlspecialchars($_SESSION['rsvp_message']); ?>
                </div>
                <?php unset($_SESSION['rsvp_message']);?>
            <?php endif; ?>

            <?php if (!empty($events)): ?>
                <ul class="events-list">
                    <?php foreach ($events as $event): ?>
                        <li class="event-item">
                            <h2><?= htmlspecialchars($event['name']); ?></h2>
                            <p><?= htmlspecialchars($event['description']); ?></p>
                            <p>Date: <?= htmlspecialchars($event['event_date']); ?></p>
                            <form method="POST" action="">
                                <input type="hidden" name="event_id" value="<?= $event['id']; ?>">
                                <button type="submit" name="status" value="attending" class="rsvp-button">RSVP</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No events found. Please check back later.</p>
            <?php endif; ?>
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
