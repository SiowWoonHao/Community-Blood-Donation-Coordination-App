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

// Calculate average rating (ignore 0)
$sqlAvg = "SELECT AVG(rating) AS avgRating 
           FROM appointment 
           WHERE eventID='$eventID' AND rating > 0";
$resultAvg = mysqli_query($conn, $sqlAvg);
$rowAvg = mysqli_fetch_assoc($resultAvg);
$averageRating = $rowAvg['avgRating'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Event</title>
</head>
<body>

<h2>Event Details</h2>
<p><a href="eventOrganizerDashboard.php">← Back to Dashboard</a></p>

<p><b>Event Name:</b> <?php echo $event['eventName']; ?></p>
<p><b>Date:</b> <?php echo $event['eventDate']; ?></p>
<p><b>Time:</b>
<?php echo substr($event['eventStartTime'],0,5) . " - " . substr($event['eventEndTime'],0,5); ?>
</p>
<p><b>Venue:</b> <?php echo $event['eventVenue']; ?></p>
<p><b>Description:</b> <?php echo $event['description']; ?></p>
<p><b>Slots:</b> <?php echo $event['availableSlots']." / ".$event['maxSlots']; ?></p>
<p><b>Status:</b> <?php echo ucfirst($event['status']); ?></p>

<p>
<b>Average Rating:</b>
<?php
if ($averageRating === null) {
    echo "No rating yet";
} else {
    echo number_format($averageRating, 1) . " / 5";
}
?>
</p>

<hr>

<h3>Donor List</h3>

<form method="GET">
    <input type="hidden" name="eventID" value="<?php echo $eventID; ?>">

    Search Name:
    <input type="text" name="search" value="<?php echo $search; ?>">

    Blood Type:
    <select name="bloodType">
        <option value="">All</option>
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

<br>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>Age</th>
        <th>Phone</th>
        <th>Blood Type</th>
        <th>Appointment Time</th>
        <th>Rating</th>
        <th>Comment</th>
    </tr>

<?php if (mysqli_num_rows($resultDonors) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($resultDonors)) { ?>
        <tr>
            <td><?php echo $row['userName']; ?></td>
            <td><?php echo $row['donorAge']; ?></td>
            <td><?php echo $row['userPhone']; ?></td>
            <td><?php echo $row['donorBloodType']; ?></td>
            <td><?php echo substr($row['appointmentTime'],0,5); ?></td>
            <td>
                <?php
                if ($row['rating'] > 0) {
                    echo $row['rating']." / 5";
                } else {
                    echo "Not rated";
                }
                ?>
            </td>
            <td>
                <?php
                if (!empty($row['comment'])) {
                    echo $row['comment'];
                } else {
                    echo "-";
                }
                ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="7" style="text-align:center;">No donors found.</td>
    </tr>
<?php } ?>

</table>

</body>
</html>
