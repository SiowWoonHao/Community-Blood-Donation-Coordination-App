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
    max-width:1200px;
    margin:40px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

.back-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:20px;
}

.filter-bar{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.filter-bar input{
    flex:1;
    padding:8px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

.actions button{
    margin-right:6px;
}
</style>
</head>

<body>

<div class="container">

<h2>Approve / Reject Event</h2>

<div class="back-bar">
    <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
</div>

<form method="GET" class="filter-bar">
    <input type="text"
           name="search"
           placeholder="Search events......"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<p><b>Showing pending events</b></p>

<table>
    <tr>
        <th>Event Name</th>
        <th>Date & Time</th>
        <th>Venue</th>
        <th>Organizer</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

<?php if (empty($events)): ?>
    <tr>
        <td colspan="6" style="text-align:center;">
            No pending events found
        </td>
    </tr>
<?php else: ?>
<?php foreach ($events as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['eventName']) ?></td>
        <td>
            <?= $e['eventDate'] ?>
            -
            <?= substr($e['eventStartTime'],0,5) ?>
        </td>
        <td><?= htmlspecialchars($e['eventVenue']) ?></td>
        <td><?= htmlspecialchars($e['organizationName']) ?></td>
        <td><?= $e['status'] ?></td>
        <td class="actions">
            <button onclick="window.location.href='approveRejectEvent.php?id=<?= $e['eventID'] ?>&action=approve'">
                Approve
            </button>
            <button onclick="window.location.href='approveRejectEvent.php?id=<?= $e['eventID'] ?>&action=reject'">
                Reject
            </button>
        </td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

</div>
</body>
</html>

