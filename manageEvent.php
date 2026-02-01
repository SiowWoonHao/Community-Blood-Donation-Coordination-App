<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Organizer") {
    echo "<script>alert('Access denied!'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['eventID'])) {
    echo "<script>alert('No event selected'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$eventID = $_GET['eventID'];
$userID = $_SESSION['userID'];

$sql = "SELECT * FROM event WHERE eventID = '$eventID' AND userID = '$userID'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "<script>alert('Event not found'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$event = mysqli_fetch_assoc($result);

if (isset($_POST['updateStatus'])) {
    $status = $_POST['status'];

    $update = "UPDATE event SET status = '$status'
               WHERE eventID = '$eventID' AND userID = '$userID'";

    if (mysqli_query($conn, $update)) {
        echo "<script>
                alert('Event status updated');
                window.location.href='eventOrganizerDashboard.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Event</title>
</head>
<body>

<h2>Manage Event</h2>

<p><b>Event:</b> <?php echo $event['eventName']; ?></p>
<p><b>Current Status:</b> <?php echo $event['status']; ?></p>

<form method="POST">
    Change Status:<br>
    <select name="status" required>
        <option value="Published" <?php if ($event['status']=="Published") echo "selected"; ?>>
            Published
        </option>
        <option value="Cancelled" <?php if ($event['status']=="Cancelled") echo "selected"; ?>>
            Cancelled
        </option>
    </select><br><br>

    <button type="submit" name="updateStatus">Update</button>
</form>

<p><a href="eventOrganizerDashboard.php">Back</a></p>

</body>
</html>
