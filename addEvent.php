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
<title>Create Event</title>

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
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:35px 45px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.back{
    border:1px solid #000;
    padding:10px;
    margin-bottom:20px;
}
.back a{
    text-decoration:none;
    color:black;
    font-weight:bold;
}

.form-box{
    border:1px solid #000;
    padding:25px;
}

label{
    display:block;
    margin-top:15px;
    font-weight:bold;
}

input, textarea{
    width:100%;
    padding:8px;
    border:1px solid #000;
    margin-top:5px;
}

textarea{
    height:120px;
}

.buttons{
    margin-top:20px;
}

button{
    padding:8px 16px;
    border:1px solid #000;
    background:#fff;
    cursor:pointer;
}

.note{
    margin-top:10px;
    font-size:14px;
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

<h2>CREATE EVENT</h2>

<div class="back">
    ← <a href="eventOrganizerDashboard.php">Back to My Events</a>
</div>

<div class="form-box">
<form method="POST">

    <label>Event Title*</label>
    <input type="text" name="eventName" required>

    <label>Event Date*</label>
    <input type="date" name="eventDate" required min="<?php echo date('Y-m-d'); ?>">

    <label>Venue / Location*</label>
    <input type="text" name="eventVenue" required>

    <label>Capacity (max donors)</label>
    <input type="number" name="maxSlots" min="1" placeholder="e.g. 100" required>

    <label>Event Start Time*</label>
    <input type="time" id="eventStartTime" name="eventStartTime" required onchange="checkTime()">

    <label>Event End Time*</label>
    <input type="time" id="eventEndTime" name="eventEndTime" required onchange="checkTime()">

    <label>Description</label>
    <textarea name="description"></textarea>

    <div class="buttons">
        <button type="submit" name="addEvent">Create Event</button>
        <a href="eventOrganizerDashboard.php">
            <button type="button">Cancel</button>
        </a>
    </div>

    <div class="note">
        Note: Event will be <b>'Pending Approve'</b> until Admin approve it
    </div>

</form>
</div>

</div>

</body>
</html>
