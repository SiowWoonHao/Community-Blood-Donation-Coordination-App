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

    $availableSlots = $maxSlots;

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

<style>
body {
    margin: 0;
    min-height: 100vh;
    font-family: Arial, sans-serif;
    background: linear-gradient(
        -45deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size: 500% 500%;
    animation: gradientMove 14s ease infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.page {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    border-radius: 14px;
    padding: 35px 45px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.18);
}

.back {
    margin-bottom: 20px;
}

.back a {
    text-decoration: none;
    color: black;
    font-weight: bold;
}

.form-group {
    margin-bottom: 18px;
}

input, textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #000;
}

textarea {
    resize: none;
}

.buttons {
    margin-top: 25px;
}

button {
    padding: 8px 18px;
    border: 1px solid #000;
    background: white;
    cursor: pointer;
    margin-right: 10px;
}

button:hover {
    background: #eee;
}

.note {
    margin-top: 10px;
    font-size: 13px;
}
</style>

<script>
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

<div class="page">

    <h2>Edit Event</h2>

    <div class="back">
        ← <a href="eventOrganizerDashboard.php">Back to My Events</a>
    </div>

    <p><strong>Edit Event:</strong> <?php echo $row['eventName']; ?></p>

    <form method="POST">

        <div class="form-group">
            Event Title*  
            <input type="text" name="eventName" required value="<?php echo $row['eventName']; ?>">
        </div>

        <div class="form-group">
            Event Date*  
            <input type="date" name="eventDate" required min="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo $row['eventDate']; ?>">
        </div>

        <div class="form-group">
            Venue / Location*  
            <input type="text" name="eventVenue" required value="<?php echo $row['eventVenue']; ?>">
        </div>

        <div class="form-group">
            Capacity (max donors)  
            <input type="number" name="maxSlots" min="1" required value="<?php echo $row['maxSlots']; ?>">
        </div>

        <div class="form-group">
            Event Start Time*  
            <input type="time" id="eventStartTime" name="eventStartTime" required
                   onchange="checkTime()" value="<?php echo $row['eventStartTime']; ?>">
        </div>

        <div class="form-group">
            Event End Time*  
            <input type="time" id="eventEndTime" name="eventEndTime" required
                   onchange="checkTime()" value="<?php echo $row['eventEndTime']; ?>">
        </div>

        <div class="form-group">
            Description  
            <textarea name="description" rows="4"><?php echo $row['description']; ?></textarea>
        </div>

        <div class="buttons">
            <button type="submit" name="updateEvent">Save Changes</button>
            <a href="eventOrganizerDashboard.php">
                <button type="button">Cancel</button>
            </a>
        </div>

        <div class="note">
            Note: Major changes may reset status to "Pending Approval"
        </div>

    </form>

</div>

</body>
</html>
