<?php
session_start();
require_once 'Database.php';

$db = new Database();
$pdo = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

$stmt = $pdo->prepare("SELECT hr.id, u.name AS user_name, hr.category, hr.description, hr.created_at FROM help_requests hr JOIN users u ON hr.user_id = u.id WHERE hr.status = 'pending'");
$stmt->execute();
$pendingRequests = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT hr.id, u.name AS user_name, hr.category, hr.description, hr.created_at FROM help_requests hr JOIN users u ON hr.user_id = u.id WHERE hr.status = 'approved'");
$stmt->execute();
$approvedRequests = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC");
$stmt->execute();
$events = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_id'])) {
        $request_id = $_POST['request_id'];
        if (isset($_POST['approve'])) {
            $stmt = $pdo->prepare("UPDATE help_requests SET status = 'approved' WHERE id = ?");
            $stmt->execute([$request_id]);
        } elseif (isset($_POST['reject'])) {
            $stmt = $pdo->prepare("UPDATE help_requests SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$request_id]);
        }
        
        header("Location: Admin.php");
        exit();
    }
}

class EventsController {
    public function getEventAttendees($event_id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT u.name FROM event_rsvps er JOIN users u ON er.user_id = u.id WHERE er.event_id = ?");
            $stmt->execute([$event_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
}

$eventsController = new EventsController();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="nav-left">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?></h1>
            </div>
            <div class="nav-center">
                <a href="?page=dashboard"><i class="fa-solid fa-house"></i></a>
                <a href="?page=help_requests"><i class="fa-solid fa-tv"></i></a>
                <a href="?page=resource_sharing"><i class="fa-solid fa-share"></i></a>
            </div>
            <div class="nav-right">
                <a href="?page=profile"><i class="fa-solid fa-user"></i></a>
                <a href="logout.php" onclick="return confirmLogout();"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
        </nav>

        <div class="main-content">
            <div class="main-left">
                <h2>Pending Help Requests</h2>
                <div class="requests-card">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingRequests)): ?>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['user_name']) ?></td>
                                        <td><?= htmlspecialchars($request['category']) ?></td>
                                        <td><?= htmlspecialchars($request['description']) ?></td>
                                        <td><?= htmlspecialchars($request['created_at']) ?></td>
                                        <td>
                                            <form action="admin.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" name="approve" class="action-button approve">Approve</button>
                                            </form>
                                            <form action="admin.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" name="reject" class="action-button reject">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No pending requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <h2>Events</h2>
                <div class="requests-card">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Attendees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($event['name']) ?></td>
                                        <td><?= htmlspecialchars($event['event_date'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($event['location'] ?? 'No Location') ?></td>
                                        <td>
                                            <ul>
                                                <?php
                                                $attendees = $eventsController->getEventAttendees($event['id']);
                                                if (!empty($attendees)): 
                                                    foreach ($attendees as $attendee): 
                                                        echo "<li>" . htmlspecialchars($attendee['name']) . "</li>";
                                                    endforeach; 
                                                else: 
                                                    echo "<li>No attendees yet.</li>";
                                                endif; 
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No events found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="main-right">
                <h2>Approved Help Requests</h2>
                <div class="requests-card">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($approvedRequests)): ?>
                                <?php foreach ($approvedRequests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['user_name']) ?></td>
                                        <td><?= htmlspecialchars($request['category']) ?></td>
                                        <td><?= htmlspecialchars($request['description']) ?></td>
                                        <td><?= htmlspecialchars($request['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No approved requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
