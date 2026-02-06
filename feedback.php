<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Admin') {
    header("Location: login.php");
    exit();
}

$feedbackStatusFilter = $_GET['feedbackStatus'] ?? '';
$search = $_GET['search'] ?? '';

$whereStatus = '';
if ($feedbackStatusFilter != '') {
    $whereStatus = " AND f.feedbackStatus='" . mysqli_real_escape_string($conn, $feedbackStatusFilter) . "'";
}

$whereSearch = '';
if ($search != '') {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $whereSearch = " AND (u.userName LIKE '%$searchEscaped%' OR e.eventName LIKE '%$searchEscaped%')";
}

$sql = "
SELECT 
    a.appointmentID,
    a.rating,
    a.comment,
    a.ratingDate,
    e.eventID,
    e.eventName,
    u.userName AS donorName,
    f.feedbackID,
    f.replyComment,
    f.replyDate,
    f.feedbackStatus
FROM appointment a
JOIN event e ON a.eventID = e.eventID
JOIN user u ON a.userID = u.userID
LEFT JOIN feedback f ON a.appointmentID = f.appointmentID
WHERE a.rating > 0
$whereStatus
$whereSearch
ORDER BY a.ratingDate DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Feedback</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #000; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
button { padding: 6px 12px; margin: 2px; cursor: pointer; }
form.search-form { margin-bottom: 15px; display: flex; gap: 10px; align-items: center; }
form.search-form input[type=text] { padding: 6px; width: 200px; }
</style>
</head>
<body>
<h2>Admin Feedback Dashboard</h2>
<div class="back">
    ← <a href="adminDashboard.php">Back to Dashboard</a>
</div>
<form class="search-form" method="GET">
    <label>Search:</label>
    <input type="text" name="search" placeholder="Donor or Event" value="<?php echo htmlspecialchars($search); ?>">
    <label>Filter by Feedback Status:</label>
    <select name="feedbackStatus">
        <option value="">All</option>
        <option value="Pending" <?php echo $feedbackStatusFilter=='Pending'?'selected':''; ?>>Pending</option>
        <option value="Replied" <?php echo $feedbackStatusFilter=='Replied'?'selected':''; ?>>Replied</option>
        <option value="Resolved" <?php echo $feedbackStatusFilter=='Resolved'?'selected':''; ?>>Resolved</option>
    </select>
    <button type="submit">Search</button>
</form>

<table>
    <tr>
        <th>Donor</th>
        <th>Event</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Rating Date</th>
        <th>Feedback Status</th>
        <th>Reply Comment</th>
        <th>Reply Date</th>
        <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['donorName']); ?></td>
        <td><?php echo htmlspecialchars($row['eventName']); ?></td>
        <td><?php echo $row['rating']; ?></td>
        <td><?php echo htmlspecialchars($row['comment']); ?></td>
        <td><?php echo $row['ratingDate'] ? date("Y-m-d H:i", strtotime($row['ratingDate'])) : ''; ?></td>
        <td><?php echo htmlspecialchars($row['feedbackStatus']); ?></td>
        <td><?php echo htmlspecialchars($row['replyComment']); ?></td>
        <td><?php echo $row['replyDate'] ? date("Y-m-d H:i", strtotime($row['replyDate'])) : ''; ?></td>
        <td>
            <form method="GET" action="replyFeedback.php" style="display:inline;">
                <input type="hidden" name="appointmentID" value="<?php echo $row['appointmentID']; ?>">
                <button type="submit">Reply</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
