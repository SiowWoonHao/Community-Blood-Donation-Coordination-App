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

/* Event Status filter */
if (!empty($_GET['eventStatus'])) {
    $eventStatus = mysqli_real_escape_string($conn, $_GET['eventStatus']);
    $sql .= " AND status = '$eventStatus'";
}

/* Time Status filter */
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
    <title>My Event</title>
    <style>
        body { margin:0; min-height:100vh; font-family:Arial,sans-serif;
            background: linear-gradient(-45deg,#f5f7fa,#b8f7d4,#9be7ff,#c7d2fe,#fef9c3);
            background-size: 500% 500%; animation: gradientMove 14s ease infinite; }
        @keyframes gradientMove {0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;}}
        .page-container {max-width:1300px; margin:50px auto; background:white; border-radius:16px; padding:30px 40px; box-shadow:0 12px 30px rgba(0,0,0,0.15);}
        .top-bar {display:flex; justify-content:space-between; align-items:center;}
        h2 {margin:0;}
        .top-actions button {margin-left:10px; padding:8px 14px; border-radius:6px; border:1px solid #ccc; background:#fff; cursor:pointer;}
        .filter-bar {margin:25px 0; display:flex; gap:10px; align-items:center;}
        .filter-bar input, .filter-bar select {padding:8px;}
        table {width:100%; border-collapse:collapse;}
        table th, table td {padding:10px; border:1px solid #ddd; text-align:left;}
        table th {background:#f4f4f4;}
        .add-btn {display:inline-block; margin:15px 0;}
        button {padding:6px 12px; border-radius:6px; border:1px solid #ccc; cursor:pointer; background:#fff;}
        button:hover {background:#eee;}
    </style>
</head>

<body>
<div class="page-container">

    <div class="top-bar">
        <h2>My Event</h2>
        <div class="top-actions">
            <button>Notification</button>
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>

    <!-- Filter & Search -->
    <form method="GET" class="filter-bar">
        <select name="eventStatus">
            <option value="">Status</option>
            <option value="published" <?= ($_GET['eventStatus'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="cancelled" <?= ($_GET['eventStatus'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <input type="text" name="search" placeholder="Search......"
               value="<?php echo $_GET['search'] ?? ''; ?>">

        <select name="timeStatus">
            <option value="">All Time</option>
            <option value="upcoming" <?= ($_GET['timeStatus'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
            <option value="past" <?= ($_GET['timeStatus'] ?? '') === 'past' ? 'selected' : '' ?>>Past</option>
        </select>

        <button type="submit">Filter</button>

        <a href="addEvent.php" class="add-btn">
            <button type="button">+ Create New Event</button>
        </a>
    </form>

    <p>Showing all events</p>

    <table>
        <tr>
            <th>Event Title</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Status</th>
            <th>Capacity</th>
            <th>Actions</th>
        </tr>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)):
                // Check if event is finished
                $isFinished = ($row['eventDate'] < $today);
                $statusText = $isFinished ? 'Finished' : ucfirst($row['status']);
            ?>
            <tr>
                <td><?= $row['eventName']; ?></td>
                <td><?= $row['eventDate']; ?></td>
                <td><?= substr($row['eventStartTime'],0,5); ?> - <?= substr($row['eventEndTime'],0,5); ?></td>
                <td><?= $row['eventVenue']; ?></td>
                <td><?= $statusText; ?></td>
                <td><?= $row['availableSlots']; ?>/<?= $row['maxSlots']; ?></td>
                <td>
                    <a href="editEvent.php?eventID=<?= $row['eventID']; ?>"><button>Edit</button></a>
                    <a href="organizerViewEvents.php?eventID=<?= $row['eventID']; ?>"><button>View Reg</button></a>
                    <?php if (!$isFinished): ?>
                        <a href="manageEvent.php?eventID=<?= $row['eventID']; ?>"><button>Manage</button></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">
                    No event matches your search criteria.
                </td>
            </tr>
        <?php endif; ?>
    </table>

</div>
</body>
</html>
