<?php
session_start();
include "db.php";

// Login & role check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Organizer') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['eventID'])) {
    header("Location: eventOrganizerDashboard.php");
    exit();
}

$eventID = $_GET['eventID'];
$organizerID = $_SESSION['userID'];

// Get event info (ensure ownership)
$sqlEvent = "SELECT * FROM event 
             WHERE eventID='$eventID' AND userID='$organizerID'";
$resultEvent = mysqli_query($conn, $sqlEvent);
$event = mysqli_fetch_assoc($resultEvent);

if (!$event) {
    header("Location: eventOrganizerDashboard.php");
    exit();
}

// Search & filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$bloodType = isset($_GET['bloodType']) ? $_GET['bloodType'] : '';

// Get donor list
$sqlDonors = "SELECT u.userName, u.userPhone, u.donorAge, u.donorBloodType,
                     a.appointmentTime, a.rating, a.comment
              FROM appointment a
              JOIN user u ON a.userID = u.userID
              WHERE a.eventID = '$eventID'";

if ($search != '') {
    $sqlDonors .= " AND u.userName LIKE '%$search%'";
}
if ($bloodType != '') {
    $sqlDonors .= " AND u.donorBloodType = '$bloodType'";
}

$resultDonors = mysqli_query($conn, $sqlDonors);

// Calculate stats
$total = mysqli_num_rows($resultDonors);
$confirmed = 0;
$cancelled = 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>View Event Registrations</title>

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

.page{
    max-width:1100px;
    margin:40px auto;
    background:#fff;
    padding:35px 45px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.back{
    margin-bottom:20px;
}
.back a{
    text-decoration:none;
    font-weight:bold;
    color:black;
}

.event-box{
    border:1px solid #000;
    padding:15px;
    margin-bottom:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.filters{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

input, select{
    padding:6px;
    border:1px solid #000;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td{
    border:1px solid #000;
    padding:8px;
    text-align:left;
}

.footer{
    margin-top:15px;
    font-size:14px;
}
</style>
</head>

<body>

<div class="page">

<h2>VIEW EVENT REGISTRATIONS</h2>

<div class="back">
    ← <a href="eventOrganizerDashboard.php">Back to My Events</a>
</div>

<div class="event-box">
    <div>
        <b>Event:</b> <?php echo $event['eventName']; ?><br>
        <b>Date:</b> <?php echo $event['eventDate']; ?> |
        <b>Time:</b> <?php echo substr($event['eventStartTime'],0,5)." - ".substr($event['eventEndTime'],0,5); ?> |
        <b>Venue:</b> <?php echo $event['eventVenue']; ?><br>
        <b>Status:</b> <?php echo ucfirst($event['status']); ?> |
        <b>Capacity:</b> <?php echo ($event['maxSlots'] - $event['availableSlots'])."/".$event['maxSlots']; ?> registered
    </div>

    <button>Export to CSV</button>
</div>

<form method="GET" class="filters">
    <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
    <select>
        <option>Sort by</option>
    </select>

    <input type="text" name="search" placeholder="Search..." value="<?php echo $search; ?>">

    <select name="bloodType">
        <option value="">All Blood Types</option>
        <?php
        $types = ['A+','A-','B+','B-','O+','O-','AB+','AB-'];
        foreach ($types as $type) {
            $selected = ($bloodType == $type) ? "selected" : "";
            echo "<option value='$type' $selected>$type</option>";
        }
        ?>
    </select>

    <button type="submit">Filter</button>
</form>

<table>
<tr>
    <th>Donor Name</th>
    <th>Age</th>
    <th>Contact</th>
    <th>Time Slot</th>
    <th>Blood Type</th>
    <th>Status</th>
</tr>

<?php
mysqli_data_seek($resultDonors, 0);
if ($total > 0) {
while ($row = mysqli_fetch_assoc($resultDonors)) {

    $status = ($row['rating'] === null) ? "Confirmed" : "Confirmed";
    $confirmed++;
?>
<tr>
    <td><?php echo $row['userName']; ?></td>
    <td><?php echo $row['donorAge']; ?></td>
    <td><?php echo $row['userPhone']; ?></td>
    <td><?php echo substr($row['appointmentTime'],0,5); ?></td>
    <td><?php echo $row['donorBloodType']; ?></td>
    <td><?php echo $status; ?></td>
</tr>
<?php }} else { ?>
<tr>
    <td colspan="6" style="text-align:center;">No donors found.</td>
</tr>
<?php } ?>
</table>

<div class="footer">
    <b>Total Registrations:</b> <?php echo $total; ?> |
    <b>Confirmed:</b> <?php echo $confirmed; ?> |
    <b>Cancelled:</b> <?php echo $cancelled; ?>
</div>

</div>

</body>
</html>

