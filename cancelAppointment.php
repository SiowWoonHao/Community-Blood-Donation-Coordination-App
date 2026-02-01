<?php
session_start();
include "db.php";

// Safety check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (!isset($_GET['appointmentID'])) {
    echo "Invalid appointment.";
    exit();
}

$appointmentID = $_GET['appointmentID'];

// Get appointment + event info
$sql = "SELECT appointment.*, event.eventName, event.eventDate, event.eventStartTime, event.eventEndTime, event.eventVenue, event.availableSlots
        FROM appointment
        JOIN event ON appointment.eventID = event.eventID
        WHERE appointment.appointmentID = '$appointmentID'
        AND appointment.userID = '$userID'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "Appointment not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);

// Handle cancel booking
if (isset($_POST['confirmCancel'])) {
    $eventID = $row['eventID'];

    // Delete appointment
    $sqlDelete = "DELETE FROM appointment WHERE appointmentID = '$appointmentID'";
    if (mysqli_query($conn, $sqlDelete)) {

        // Increase available slots
        $sqlUpdateSlots = "UPDATE event
                           SET availableSlots = availableSlots + 1
                           WHERE eventID = '$eventID'";
        mysqli_query($conn, $sqlUpdateSlots);

        echo "<script>
                alert('Appointment cancelled successfully!');
                window.location.href = 'donorProfile.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Failed to cancel appointment');</script>";
    }
}

// Handle keep appointment
if (isset($_POST['keep'])) {
    header("Location: donorProfile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointment</title>
</head>
<body>

<h2>Cancel Appointment</h2>

<p><b>Event:</b> <?php echo $row['eventName']; ?></p>
<p><b>Date:</b> <?php echo $row['eventDate']; ?></p>
<p><b>Time:</b> <?php echo substr($row['eventStartTime'],0,5) . " - " . substr($row['eventEndTime'],0,5); ?></p>
<p><b>Venue:</b> <?php echo $row['eventVenue']; ?></p>
<p><b>Your Appointment Time:</b> <?php echo substr($row['appointmentTime'],0,5); ?></p>

<hr>

<p>Are you sure you want to cancel this appointment?</p>

<form method="POST">
    <button type="submit" name="confirmCancel">Yes, Cancel Appointment</button>
    <button type="submit" name="keep">No, Keep Appointment</button>
</form>

</body>
</html>
