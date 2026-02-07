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
body{
    margin:0;
    min-height:100vh;
    font-family:Arial, sans-serif;
    background: linear-gradient(
        -45deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:500% 500%;
    animation: gradientMove 14s ease infinite;
}
@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.page-container{
    max-width:1300px;
    margin:60px auto;
    background:white;
    border-radius:18px;
    padding:35px 45px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.top-actions button{
    padding:8px 16px;
    border-radius:8px;
    border:1px solid #ccc;
    background:#fff;
    cursor:pointer;
    font-weight:500;
}

.top-actions button:hover{
    background:#f2f2f2;
}

.filter-bar{
    display:flex;
    gap:12px;
    align-items:center;
    margin-bottom:20px;
    flex-wrap:wrap;
}

.filter-bar input,
.filter-bar select{
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #ccc;
}

.filter-bar button{
    padding:10px 18px;
    border-radius:8px;
    border:1px solid #6a5cff;
    background:#6a5cff;
    color:white;
    font-weight:600;
    cursor:pointer;
}

.filter-bar button:hover{
    opacity:0.9;
}

.add-btn button{
    background:#22c55e;
    border:1px solid #22c55e;
    color:white;
    font-weight:600;
}

.add-btn button:hover{
    opacity:0.9;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

table th{
    background:#f4f6fb;
    padding:12px;
    border:1px solid #ddd;
    text-align:left;
}

table td{
    padding:12px;
    border:1px solid #ddd;
}

.status-finished{
    color:#888;
    font-weight:600;
}

.action-btn{
    padding:6px 12px;
    border-radius:6px;
    border:1px solid #ccc;
    background:#fff;
    cursor:pointer;
    margin-right:5px;
}

.action-btn:hover{
    background:#eee;
}
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

        <input type="text" name="search" placeholder="Search event name..."
               value="<?= $_GET['search'] ?? ''; ?>">

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
                $isFinished = ($row['eventDate'] < $today);
                $statusText = $isFinished ? 'Finished' : ucfirst($row['status']);
            ?>
            <tr>
                <td><?= $row['eventName']; ?></td>
                <td><?= $row['eventDate']; ?></td>
                <td><?= substr($row['eventStartTime'],0,5); ?> - <?= substr($row['eventEndTime'],0,5); ?></td>
                <td><?= $row['eventVenue']; ?></td>
                <td class="<?= $isFinished ? 'status-finished' : ''; ?>">
                    <?= $statusText; ?>
                </td>
                <td><?= $row['availableSlots']; ?>/<?= $row['maxSlots']; ?></td>
                <td>
                    <a href="editEvent.php?eventID=<?= $row['eventID']; ?>"><button class="action-btn">Edit</button></a>
                    <a href="organizerViewEvents.php?eventID=<?= $row['eventID']; ?>"><button class="action-btn">View Reg</button></a>
                    <?php if (!$isFinished): ?>
                        <a href="manageEvent.php?eventID=<?= $row['eventID']; ?>"><button class="action-btn">Manage</button></a>
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

