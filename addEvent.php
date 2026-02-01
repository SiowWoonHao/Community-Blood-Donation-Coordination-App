<?php
session_start();
include "db.php";

// Permission check: only Organizer
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Organizer") {
    echo "<script>alert('Access denied!'); window.location.href='login.php';</script>";
    exit();
}

$userID = $_SESSION['userID'];

if (isset($_POST['addEvent'])) {
    $eventName      = $_POST['eventName'];
    $eventDate      = $_POST['eventDate'];
    $eventVenue     = $_POST['eventVenue'];
    $maxSlots       = $_POST['maxSlots'];
    $eventStartTime = $_POST['eventStartTime'];
    $eventEndTime   = $_POST['eventEndTime'];
    $description    = $_POST['description'];

    $availableSlots = $maxSlots;
    $status = "Published";

    // Insert into event table
    $sql = "INSERT INTO event 
        (userID, eventName, eventDate, eventVenue, availableSlots, maxSlots, 
         eventStartTime, eventEndTime, description, status)
        VALUES 
        ('$userID', '$eventName', '$eventDate', '$eventVenue', 
         '$availableSlots', '$maxSlots',
         '$eventStartTime', '$eventEndTime', '$description', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Event added successfully!');
                window.location.href = 'eventOrganizerDashboard.php';
              </script>";
    } else {
        echo "<script>alert('Failed to add event');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
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

<h2>Add New Event</h2>

<form method="POST">
    Event Name*:<br>
    <input type="text" name="eventName" required><br><br>

    Event Date*:<br>
    <input type="date" name="eventDate" required min="<?php echo date('Y-m-d'); ?>"><br><br>

    Event Venue*:<br>
    <input type="text" name="eventVenue" required><br><br>

    Max Slots*:<br>
    <input type="number" name="maxSlots" min="1" required><br><br>

    Event Start Time*:<br>
    <input type="time" id="eventStartTime" name="eventStartTime" required onchange="checkTime()"><br><br>

    Event End Time*:<br>
    <input type="time" id="eventEndTime" name="eventEndTime" required onchange="checkTime()"><br><br>

    Description:<br>
    <textarea name="description" rows="4" cols="50"></textarea><br><br>

    <button type="submit" name="addEvent">Add Event</button>
</form>

<p><a href="eventOrganizerDashboard.php">Back to Dashboard</a></p>

</body>
</html>
