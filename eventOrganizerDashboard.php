<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'Organizer') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$today = date('Y-m-d');

$sql = "SELECT * FROM event WHERE userID = $userID";

/* Search by event name */
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " AND eventName LIKE '%$search%'";
}

/* Event Status filter (published / cancelled only) */
if (!empty($_GET['eventStatus'])) {
    $eventStatus = mysqli_real_escape_string($conn, $_GET['eventStatus']);
    $sql .= " AND status = '$eventStatus'";
}

/* Time Status filter (upcoming / past) */
if (!empty($_GET['timeStatus'])) {
    if ($_GET['timeStatus'] === 'upcoming') {
        $sql .= " AND eventDate >= '$today'";
    } elseif ($_GET['timeStatus'] === 'past') {
        $sql .= " AND eventDate < '$today'";
    }
}

$sql .= " ORDER BY eventDate ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Organizer Dashboard</title>
</head>
<body>

<h2>My Events</h2>
<form action="logout.php" method="post" style="margin-bottom:15px;">
    <button type="submit">Logout</button>
</form>

<!-- Filter & Search -->
<form method="GET" style="margin-bottom:20px;">
    <input type="text" name="search" placeholder="Search event title"
           value="<?php echo $_GET['search'] ?? ''; ?>">

    <select name="eventStatus">
        <option value="">All Event Status</option>
        <option value="published" <?= ($_GET['eventStatus'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="cancelled" <?= ($_GET['eventStatus'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <select name="timeStatus">
        <option value="">All Time</option>
        <option value="upcoming" <?= ($_GET['timeStatus'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
        <option value="past" <?= ($_GET['timeStatus'] ?? '') === 'past' ? 'selected' : '' ?>>Past</option>
    </select>

    <button type="submit">Filter</button>
    <a href="eventOrganizerDashboard.php">Reset</a>
</form>

<a href="addEvent.php">+ Add Event</a>

<br><br>

<table border="1" width="100%" cellpadding="8">
    <tr>
        <th>Title</th>
        <th>Date</th>
        <th>Time</th>
        <th>Venue</th>
        <th>Slots</th>
        <th>Status</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

<?php if (mysqli_num_rows($result) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['eventName']; ?></td>
            <td><?= $row['eventDate']; ?></td>
            <td>
                <?= substr($row['eventStartTime'], 0, 5); ?>
                -
                <?= substr($row['eventEndTime'], 0, 5); ?>
            </td>
            <td><?= $row['eventVenue']; ?></td>
            <td><?= $row['availableSlots']; ?>/<?= $row['maxSlots']; ?></td>
            <td><?= ucfirst($row['status']); ?></td>
            <td><?= $row['description']; ?></td>
            <td>
                <a href="editEvent.php?eventID=<?= $row['eventID']; ?>">Edit</a> |
                <a href="manageEvent.php?eventID=<?= $row['eventID']; ?>">Manage</a>
            </td>
        </tr>
    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="8" style="text-align:center;">
            No event matches your search criteria.
        </td>
    </tr>

<?php endif; ?>

</table>

</body>
</html>
