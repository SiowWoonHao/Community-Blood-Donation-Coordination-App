<?php
session_start();
include "db.php";

// Safety check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Organizer') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (!isset($_GET['eventID'])) {
    echo "Invalid event.";
    exit();
}

$eventID = $_GET['eventID'];

// Get event info
$sqlEvent = "SELECT * FROM event WHERE eventID = '$eventID' AND userID = '$userID'";
$resultEvent = mysqli_query($conn, $sqlEvent);

if (mysqli_num_rows($resultEvent) != 1) {
    echo "Event not found.";
    exit();
}

$event = mysqli_fetch_assoc($resultEvent);

// Handle search & blood type filter
$searchName = isset($_GET['searchName']) ? mysqli_real_escape_string($conn, $_GET['searchName']) : '';
$filterBlood = isset($_GET['filterBlood']) ? mysqli_real_escape_string($conn, $_GET['filterBlood']) : '';

// Get all donors for this event with search/filter
$sqlDonors = "SELECT u.userName, u.donorAge, u.userPhone, u.donorBloodType, a.appointmentTime
              FROM appointment a
              JOIN user u ON a.userID = u.userID
              WHERE a.eventID = '$eventID'";

if ($searchName != '') {
    $sqlDonors .= " AND u.userName LIKE '%$searchName%'";
}
if ($filterBlood != '' && $filterBlood != 'all') {
    $sqlDonors .= " AND u.donorBloodType = '$filterBlood'";
}

$sqlDonors .= " ORDER BY a.appointmentTime ASC";

$resultDonors = mysqli_query($conn, $sqlDonors);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Event</title>
</head>
<body>

<h2>Event Details</h2>

<p><b>Event Name:</b> <?php echo $event['eventName']; ?></p>
<p><b>Date:</b> <?php echo $event['eventDate']; ?></p>
<p><b>Time:</b> <?php echo substr($event['eventStartTime'],0,5) . " - " . substr($event['eventEndTime'],0,5); ?></p>
<p><b>Venue:</b> <?php echo $event['eventVenue']; ?></p>
<p><b>Max Slots:</b> <?php echo $event['maxSlots']; ?></p>
<p><b>Available Slots:</b> <?php echo $event['availableSlots']; ?></p>
<p><b>Description:</b> <?php echo $event['description']; ?></p>

<hr>

<h3>Donor List</h3>

<!-- Search Form -->
<form method="GET" action="">
    <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">
    Search Name: <input type="text" name="searchName" value="<?php echo htmlspecialchars($searchName); ?>">
    Blood Type: 
    <select name="filterBlood">
        <option value="all">All</option>
        <option value="A+" <?php if($filterBlood=='A+') echo 'selected';?>>A+</option>
        <option value="A-" <?php if($filterBlood=='A-') echo 'selected';?>>A-</option>
        <option value="B+" <?php if($filterBlood=='B+') echo 'selected';?>>B+</option>
        <option value="B-" <?php if($filterBlood=='B-') echo 'selected';?>>B-</option>
        <option value="O+" <?php if($filterBlood=='O+') echo 'selected';?>>O+</option>
        <option value="O-" <?php if($filterBlood=='O-') echo 'selected';?>>O-</option>
        <option value="AB+" <?php if($filterBlood=='AB+') echo 'selected';?>>AB+</option>
        <option value="AB-" <?php if($filterBlood=='AB-') echo 'selected';?>>AB-</option>
    </select>
    <button type="submit">Search</button>
</form>

<?php if(mysqli_num_rows($resultDonors) > 0) { ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Donor Name</th>
            <th>Age</th>
            <th>Phone</th>
            <th>Blood Type</th>
            <th>Appointment Time</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($resultDonors)) { ?>
            <tr>
                <td><?php echo $row['userName']; ?></td>
                <td><?php echo $row['donorAge']; ?></td>
                <td><?php echo $row['userPhone']; ?></td>
                <td><?php echo $row['donorBloodType']; ?></td>
                <td><?php echo substr($row['appointmentTime'],0,5); ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } else { ?>
    <p>No donors found for this search/filter.</p>
<?php } ?>

<br>
<form action="eventOrganizerDashboard.php">
    <button type="submit">Back to Dashboard</button>
</form>

</body>
</html>
