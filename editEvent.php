<?php
session_start();
include "db.php";

// Permission check: only Organizer
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Organizer") {
    echo "<script>alert('Access denied!'); window.location.href='login.php';</script>";
    exit();
}

// Check if eventID is provided
if (!isset($_GET['eventID'])) {
    echo "<script>alert('No event specified!'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$eventID = $_GET['eventID'];
$userID = $_SESSION['userID'];

// Fetch event data
$sql = "SELECT * FROM event WHERE eventID = '$eventID' AND userID = '$userID'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "<script>alert('Event not found!'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$row = mysqli_fetch_assoc($result);

// Handle form submission
if (isset($_POST['updateEvent'])) {
    $eventName      = $_POST['eventName'];
    $eventDate      = $_POST['eventDate'];
    $eventVenue     = $_POST['eventVenue'];
    $maxSlots       = $_POST['maxSlots'];
    $eventStartTime = $_POST['eventStartTime'];
    $eventEndTime   = $_POST['eventEndTime'];
    $description    = $_POST['description'];

    // Always reset availableSlots to maxSlots
    $availableSlots = $maxSlots;

    // Update event in database
    $sqlUpdate = "UPDATE event SET
                    eventName = '$eventName',
                    eventDate = '$eventDate',
                    eventVenue = '$eventVenue',
                    maxSlots = '$maxSlots',
                    availableSlots = '$availableSlots',
                    eventStartTime = '$eventStartTime',
                    eventEndTime = '$eventEndTime',
                    description = '$description'
                  WHERE eventID = '$eventID' AND userID = '$userID'";

    if (mysqli_query($conn, $sqlUpdate)) {
        echo "<script>
                alert('Event updated successfully!');
                window.location.href='eventOrganizerDashboard.php';
              </script>";
    } else {
        echo "<script>alert('Failed to update event');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <script>
    // Ensure end time >= start time
    function checkTime() {
        const start = document.getElementById('eventStartTime').value;
        const end = document.getElementById('eventEndTime').value;
        if (end && start && end < start) {
            alert("End time cannot be earlier than start time!");
            document.getElementById('eventEndTime').value = "";
        }
    }
    </script>
</head>
<body>

<h2>Edit Event</h2>

<form method="POST">
    Event Name*:<br>
    <input type="text" name="eventName" required value="<?php echo $row['eventName']; ?>"><br><br>

    Event Date*:<br>
    <input type="date" name="eventDate" required min="<?php echo date('Y-m-d'); ?>" 
           value="<?php echo $row['eventDate']; ?>"><br><br>

    Event Venue*:<br>
    <input type="text" name="eventVenue" required value="<?php echo $row['eventVenue']; ?>"><br><br>

    Max Slots*:<br>
    <input type="number" name="maxSlots" min="1" required value="<?php echo $row['maxSlots']; ?>"><br><br>

    Event Start Time*:<br>
    <input type="time" id="eventStartTime" name="eventStartTime" required onchange="checkTime()" 
           value="<?php echo $row['eventStartTime']; ?>"><br><br>

    Event End Time*:<br>
    <input type="time" id="eventEndTime" name="eventEndTime" required onchange="checkTime()" 
           value="<?php echo $row['eventEndTime']; ?>"><br><br>

    Description:<br>
    <textarea name="description" rows="4" cols="50"><?php echo $row['description']; ?></textarea><br><br>

    <button type="submit" name="updateEvent">Update Event</button>
</form>

<p><a href="eventOrganizerDashboard.php">Back to Dashboard</a></p>

</body>
</html>
