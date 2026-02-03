<?php
include 'db.php';

$eventID = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if (empty($eventID)) die("Event ID is required");
if ($action !== 'approve' && $action !== 'reject') die("Invalid action");

// Fetch event info
$sql = "SELECT event.*, user.organizationName 
        FROM event 
        JOIN user ON event.userID = user.userID 
        WHERE eventID=$eventID";

$event = $conn->query($sql)->fetch_assoc();
if (!$event) die("Event not found");

// Handle approve/reject submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['decision'];
    $conn->query("UPDATE event SET status='$status' WHERE eventID=$eventID");
    header("Location: approveEvents.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Approve / Reject Event</title>

<style>
body{
    margin:0;
    min-height:100vh;
    font-family: Arial, sans-serif;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:1000px;
    margin:50px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

.back-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:25px;
}

.event-card{
    border:2px solid #ccc;
    padding:20px;
    margin-bottom:30px;
}

.event-card p{
    margin:6px 0;
}

.actions{
    text-align:center;
}

.actions button{
    padding:10px 20px;
    margin:0 10px;
    font-size:14px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="container">

<h2>Approve / Reject Event</h2>

<div class="back-bar">
    <a href="adminDashboard.php">← Back to Admin Dashboard</a>
</div>

<div class="event-card">
    <p><b>Event:</b> <?= htmlspecialchars($event['eventName']) ?></p>
    <p><b>Organizer:</b> <?= htmlspecialchars($event['organizationName']) ?></p>
    <p><b>Date:</b> <?= $event['eventDate'] ?></p>
    <p><b>Time:</b>
        <?= substr($event['eventStartTime'],0,5) ?>
        -
        <?= substr($event['eventEndTime'],0,5) ?>
    </p>
    <p><b>Venue:</b> <?= htmlspecialchars($event['eventVenue']) ?></p>
    <p><b>Slots:</b> <?= $event['availableSlots'] ?> / <?= $event['maxSlots'] ?></p>
    <p><b>Status:</b> <?= $event['status'] ?></p>
</div>

<form method="POST" class="actions">
    <?php if ($action === 'approve'): ?>
        <button type="submit" name="decision" value="Published">
            Approve
        </button>
    <?php else: ?>
        <button type="submit" name="decision" value="Rejected">
            Reject
        </button>
    <?php endif; ?>

    <button type="button"
            onclick="window.location.href='approveEvents.php'">
        Cancel
    </button>
</form>

</div>
</body>
</html>

