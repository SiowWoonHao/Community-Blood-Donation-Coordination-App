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
/* ===== SAME GRADIENT AS OTHER ADMIN PAGES ===== */
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

/* ===== CONTAINER ===== */
.container{
    max-width:1200px;
    margin:50px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

/* ===== TOP BAR ===== */
.top-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:20px;
}

/* ===== FILTER / SEARCH ROW ===== */
.filter-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
}

.filter-left{
    display:flex;
    gap:10px;
}

.filter-right input{
    padding:8px;
    width:220px;
}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

th{
    background:#f2f2f2;
}

button{
    padding:6px 12px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="container">

<h2>Feedback</h2>

<div class="top-bar">
    ← <a href="adminDashboard.php">Back to Admin Dashboard</a>
</div>

<form method="GET">
<div class="filter-row">

    <div class="filter-left">
        <select name="feedbackStatus">
            <option value="">All</option>
            <option value="Pending" <?= $feedbackStatusFilter=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Resolved" <?= $feedbackStatusFilter=='Resolved'?'selected':'' ?>>Resolved</option>
            <option value="Replied" <?= $feedbackStatusFilter=='Replied'?'selected':'' ?>>Replied</option>
        </select>
    </div>

    <div class="filter-right">
        <input type="text"
               name="search"
               placeholder="Search feedback……"
               value="<?= htmlspecialchars($search) ?>">
    </div>

</div>
</form>

<p>Showing feedback</p>

<table>
<tr>
    <th>Donor</th>
    <th>Event</th>
    <th>Rating</th>
    <th>Comment</th>
    <th>Rating Date</th>
    <th>Status</th>
    <th>Reply Comment</th>
    <th>Reply Date</th>
    <th>Actions</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= htmlspecialchars($row['donorName']) ?></td>
    <td><?= htmlspecialchars($row['eventName']) ?></td>
    <td><?= $row['rating'] ?></td>
    <td><?= htmlspecialchars($row['comment']) ?></td>
    <td><?= $row['ratingDate'] ? date("Y-m-d", strtotime($row['ratingDate'])) : '' ?></td>
    <td><?= htmlspecialchars($row['feedbackStatus']) ?></td>
    <td><?= htmlspecialchars($row['replyComment']) ?></td>
    <td><?= $row['replyDate'] ? date("Y-m-d", strtotime($row['replyDate'])) : '' ?></td>
    <td>
        <form method="GET" action="replyFeedback.php">
            <input type="hidden" name="appointmentID" value="<?= $row['appointmentID'] ?>">
            <button type="submit">View</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</body>
</html>
