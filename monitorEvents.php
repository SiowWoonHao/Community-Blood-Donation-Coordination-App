<?php
include 'db.php';

$search = $_GET['search'] ?? '';
$onlyFlagged = isset($_GET['onlyFlagged']) ? true : false;

// Handle flag/unflag action
if (isset($_GET['flag_action']) && isset($_GET['id'])) {
    $eventID = $_GET['id'];
    $currentFlag = $_GET['flag_action']; // 'Flag' or 'Unflag'
    $newFlag = ($currentFlag === 'Flag') ? 'Unflag' : 'Flag';
    $conn->query("UPDATE event SET flagStatus='$newFlag' WHERE eventID=$eventID");
    header("Location: monitorEvents.php?search=$search" . ($onlyFlagged ? "&onlyFlagged=1" : ""));
    exit;
}

// Fetch events
$sql = "SELECT event.*, user.organizationName 
        FROM event 
        JOIN user ON event.userID = user.userID 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (eventName LIKE '%$search%' OR eventVenue LIKE '%$search%')";
}

if ($onlyFlagged) {
    $sql .= " AND flagStatus='Flag'";
}

$sql .= " ORDER BY eventDate, eventStartTime";

$events = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monitor Events</title>
</head>
<body>
    <h1>Monitor Events</h1>
    <p><a href="adminDashboard.php">Back to Dashboard</a></p>
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search by name or venue" value="<?php echo htmlspecialchars($search); ?>">
        <label>
            <input type="checkbox" name="onlyFlagged" value="1" <?php echo $onlyFlagged ? 'checked' : ''; ?>>
            Only Flagged
        </label>
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='monitorEvents.php'">Reset</button>
    </form>

    <table border="1" cellpadding="8">
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
            <th>Flag</th>
            <th>Action</th>
        </tr>

        <?php if (empty($events)): ?>
            <tr><td colspan="11" style="text-align:center;">No events found.</td></tr>
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
                <td><?php echo $e['flagStatus'] ?? 'Unflag'; ?></td>
                <td>
                    <button onclick="window.location.href='viewEvent.php?id=<?php echo $e['eventID']; ?>'">View</button>
                    <button onclick="window.location.href='monitorEvents.php?id=<?php echo $e['eventID']; ?>&flag_action=<?php echo ($e['flagStatus'] ?? 'Unflag'); ?>&search=<?php echo urlencode($search); ?><?php echo $onlyFlagged ? '&onlyFlagged=1' : ''; ?>'">
                        <?php echo ($e['flagStatus'] ?? 'Unflag') === 'Flag' ? 'Unflag' : 'Flag'; ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</body>
</html>
