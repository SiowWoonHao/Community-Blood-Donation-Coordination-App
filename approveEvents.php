<?php
include 'db.php';

$search = $_GET['search'] ?? '';

// Fetch pending events
$sql = "SELECT event.*, user.organizationName 
        FROM event 
        JOIN user ON event.userID = user.userID
        WHERE status='Pending'";

if (!empty($search)) {
    $sql .= " AND (eventName LIKE '%$search%' OR eventVenue LIKE '%$search%')";
}

$sql .= " ORDER BY eventDate, eventStartTime";

$events = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Events</title>
</head>
<body>
    <h1>Approve Pending Events</h1>
    <p><a href="adminDashboard.php">Back to Dashboard</a></p>
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name or venue" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='approveEvent.php'">Reset</button>
    </form>

    <table border="1" cellpadding="8" style="margin-top:20px;">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Organizer</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Available Slots</th>
            <th>Max Slots</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if (empty($events)): ?>
            <tr><td colspan="10" style="text-align:center;">No pending events found.</td></tr>
        <?php else: ?>
            <?php foreach ($events as $e): ?>
            <tr>
                <td><?php echo $e['eventID']; ?></td>
                <td><?php echo htmlspecialchars($e['eventName']); ?></td>
                <td><?php echo htmlspecialchars($e['organizationName']); ?></td>
                <td><?php echo $e['eventDate']; ?></td>
                <td><?php echo date('H:i', strtotime($e['eventStartTime'])) . " - " . date('H:i', strtotime($e['eventEndTime'])); ?></td>
                <td><?php echo htmlspecialchars($e['eventVenue']); ?></td>
                <td><?php echo $e['availableSlots']; ?></td>
                <td><?php echo $e['maxSlots']; ?></td>
                <td><?php echo $e['status']; ?></td>
                <td>
                    <button onclick="window.location.href='approveRejectEvent.php?id=<?php echo $e['eventID']; ?>&action=approve'">Approve</button>
                    <button onclick="window.location.href='approveRejectEvent.php?id=<?php echo $e['eventID']; ?>&action=reject'">Reject</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</body>
</html>
