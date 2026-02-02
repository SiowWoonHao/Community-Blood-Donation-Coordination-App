<?php
session_start();
include "db.php";

// Login & role check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get appointment ID
if (!isset($_GET['appointmentID'])) {
    header("Location: donorProfile.php");
    exit();
}

$appointmentID = $_GET['appointmentID'];

// Get appointment + event info
$sql = "SELECT a.*, e.eventName, e.eventDate, e.eventVenue, e.eventEndTime
        FROM appointment a
        JOIN event e ON a.eventID = e.eventID
        WHERE a.appointmentID='$appointmentID' AND a.userID='$userID'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

// Invalid appointment
if (!$data) {
    header("Location: donorProfile.php");
    exit();
}

// Check event already ended
$eventEndDateTime = strtotime($data['eventDate'] . ' ' . $data['eventEndTime']);
if ($eventEndDateTime > time()) {
    header("Location: donorProfile.php");
    exit();
}

// Prevent duplicate feedback
if ($data['rating'] > 0) {
    echo "<script>
            alert('You have already submitted feedback.');
            window.location.href='donorProfile.php';
          </script>";
    exit();
}

// Handle submit
if (isset($_POST['submit'])) {
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $update = "UPDATE appointment 
               SET rating='$rating', comment='$comment'
               WHERE appointmentID='$appointmentID'";
    mysqli_query($conn, $update);

    echo "<script>
            alert('Thank you for your feedback!');
            window.location.href='donorProfile.php';
          </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Feedback</title>
</head>
<body>

<h2>Give Feedback</h2>
<p><a href="donorProfile.php">← Back to Profile</a></p>

<h3>Event Information</h3>
<p><b>Event:</b> <?php echo $data['eventName']; ?></p>
<p><b>Date:</b> <?php echo $data['eventDate']; ?></p>
<p><b>Venue:</b> <?php echo $data['eventVenue']; ?></p>

<hr>

<form method="POST">

    <label>Rating (1 - 5)</label><br>
    <select name="rating" required>
        <option value="">-- Select --</option>
        <option value="1">1 - Very Bad</option>
        <option value="2">2 - Bad</option>
        <option value="3">3 - Average</option>
        <option value="4">4 - Good</option>
        <option value="5">5 - Excellent</option>
    </select>
    <br><br>

    <label>Comment</label><br>
    <textarea name="comment" rows="5" cols="50" required></textarea>
    <br><br>

    <button type="submit" name="submit">Submit Feedback</button>
</form>

</body>
</html>
