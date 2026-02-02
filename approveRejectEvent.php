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
<html>
<head>
    <title>Event Decision</title>
</head>
<body>
    <h1>Event Decision</h1>

    <p><strong>ID:</strong> <?php echo $event['eventID']; ?></p>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($event['eventName']); ?></p>
    <p><strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizationName']); ?></p>
    <p><strong>Date:</strong> <?php echo $event['eventDate']; ?></p>
    <p><strong>Time:</strong> <?php echo date('H:i', strtotime($event['eventStartTime'])) . " - " . date('H:i', strtotime($event['eventEndTime'])); ?></p>
    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['eventVenue']); ?></p>
    <p><strong>Available Slots:</strong> <?php echo $event['availableSlots']; ?></p>
    <p><strong>Max Slots:</strong> <?php echo $event['maxSlots']; ?></p>
    <p><strong>Status:</strong> <?php echo $event['status']; ?></p>

    <form method="POST">
        <?php if ($action === 'approve'): ?>
            <button type="submit" name="decision" value="Published">Approve</button>
        <?php elseif ($action === 'reject'): ?>
            <button type="submit" name="decision" value="Rejected">Reject</button>
        <?php endif; ?>
        <button type="button" onclick="window.location.href='approveEvents.php'">Cancel</button>
    </form>
</body>
</html>
