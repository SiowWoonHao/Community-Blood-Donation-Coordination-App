<?php
include 'db.php';

$search = $_GET['search'] ?? '';
$onlyFlagged = isset($_GET['onlyFlagged']) ? true : false;

// Handle flag/unflag action
if (isset($_GET['flag_action']) && isset($_GET['id'])) {
    $eventID = $_GET['id'];
    $currentFlag = $_GET['flag_action'];
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monitor Events</title>

<style>
body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(
        -45deg,
        #9ef0e1,
        #b8e7ff,
        #c6b8ff,
        #9ef0e1
    );
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    width: 90%;
    max-width: 1300px;
    margin: 40px auto;
    padding: 30px 40px;
    background: white;
    border-radius: 22px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* ===== Header ===== */
h1 {
    margin-top: 0;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #6a5cff;
    text-decoration: none;
    font-weight: 500;
}

/* ===== Filters ===== */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.filter-bar input[type="text"] {
    flex: 1;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #ccc;
}

.filter-bar label {
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-bar button {
    padding: 10px 16px;
    border-radius: 10px;
    border: none;
    background: #6a5cff;
    color: white;
    cursor: pointer;
}

.filter-bar button:hover {
    opacity: 0.85;
}

/* ===== Table ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f2f3f7;
    text-align: left;
    padding: 12px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background: #f9f9ff;
}

/* ===== Buttons ===== */
td button {
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #6a5cff;
    background: white;
    cursor: pointer;
}

td button:hover {
    background: #6a5cff;
    color: white;
}
</style>
</head>

<body>

<div class="container">

    <h1>Monitor Events</h1>
    <a class="back-link" href="adminDashboard.php">← Back to Dashboard</a>

    <form method="GET" class="filter-bar">
        <input
            type="text"
            name="search"
            placeholder="Search by name or venue"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <label>
            <input type="checkbox" name="onlyFlagged" value="1" <?php echo $onlyFlagged ? 'checked' : ''; ?>>
            Only Flagged
        </label>

        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='monitorEvents.php'">Reset</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Organizer</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Available</th>
            <th>Max</th>
            <th>Status</th>
            <th>Flag</th>
            <th>Action</th>
        </tr>

        <?php if (empty($events)): ?>
            <tr>
                <td colspan="11" style="text-align:center;">No events found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($events as $e): ?>
            <tr>
                <td><?php echo $e['eventID']; ?></td>
                <td><?php echo htmlspecialchars($e['eventName']); ?></td>
                <td><?php echo htmlspecialchars($e['organizationName']); ?></td>
                <td><?php echo $e['eventDate']; ?></td>
                <td>
                    <?php
                        echo date('H:i', strtotime($e['eventStartTime'])) .
                        " - " .
                        date('H:i', strtotime($e['eventEndTime']));
                    ?>
                </td>
                <td><?php echo htmlspecialchars($e['eventVenue']); ?></td>
                <td><?php echo $e['availableSlots']; ?></td>
                <td><?php echo $e['maxSlots']; ?></td>
                <td><?php echo $e['status']; ?></td>
                <td><?php echo $e['flagStatus'] ?? 'Unflag'; ?></td>
                <td>
                    <button onclick="window.location.href='viewEvent.php?id=<?php echo $e['eventID']; ?>'">
                        View
                    </button>
                    <button onclick="window.location.href='monitorEvents.php?id=<?php echo $e['eventID']; ?>&flag_action=<?php echo ($e['flagStatus'] ?? 'Unflag'); ?>&search=<?php echo urlencode($search); ?><?php echo $onlyFlagged ? '&onlyFlagged=1' : ''; ?>'">
                        <?php echo ($e['flagStatus'] ?? 'Unflag') === 'Flag' ? 'Unflag' : 'Flag'; ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

</div>

</body>
</html>
