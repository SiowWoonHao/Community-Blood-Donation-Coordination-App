<?php
session_start();
include "db.php";

// Safety check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
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
$sqlEvent = "SELECT * FROM event
             WHERE eventID = '$eventID'
             AND status = 'published'
             AND eventDate >= CURDATE()";

$resultEvent = mysqli_query($conn, $sqlEvent);

if (mysqli_num_rows($resultEvent) != 1) {
    echo "Event not available.";
    exit();
}

$event = mysqli_fetch_assoc($resultEvent);

// Handle booking
if (isset($_POST['book'])) {

    if ($event['availableSlots'] <= 0) {
        echo "<script>alert('This event is full');</script>";
    } else {

        // Check duplicate appointment
        $checkSql = "SELECT * FROM appointment
                     WHERE userID = '$userID'
                     AND eventID = '$eventID'";
        $checkResult = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($checkResult) > 0) {

            echo "<script>
                    alert('You have already booked this event.');
                    window.location.href = 'viewEvents.php';
                  </script>";
            exit();

        }

        $appointmentTime = $_POST['appointmentTime'];

        // Insert appointment
        $sqlInsert = "INSERT INTO appointment
            (userID, eventID, appointmentDate, appointmentTime, appointmentVenue)
            VALUES
            ('$userID', '$eventID', '{$event['eventDate']}', '$appointmentTime', '{$event['eventVenue']}')";

        if (mysqli_query($conn, $sqlInsert)) {

            // Reduce available slots
            $sqlUpdate = "UPDATE event
                          SET availableSlots = availableSlots - 1
                          WHERE eventID = '$eventID'";

            mysqli_query($conn, $sqlUpdate);

            echo "<script>
                    alert('Appointment booked successfully!');
                    window.location.href = 'viewEvents.php';
                  </script>";
        } else {
            echo "<script>alert('Booking failed');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
</head>
<body>

<h2>Book Appointment</h2>

<h3>Event Information</h3>

<p><b>Event:</b> <?php echo $event['eventName']; ?></p>
<p><b>Date:</b> <?php echo $event['eventDate']; ?></p>
<p><b>Time:</b>
    <?php
        echo substr($event['eventStartTime'], 0, 5)
        . " - " .
        substr($event['eventEndTime'], 0, 5);
    ?>
</p>
<p><b>Venue:</b> <?php echo $event['eventVenue']; ?></p>
<p><b>Slots:</b>
    <?php echo $event['availableSlots'] . " / " . $event['maxSlots']; ?>
</p>

<hr>

<?php if ($event['availableSlots'] > 0) { ?>

<form method="POST">

    <label>Select Appointment Time:</label><br>
    <input type="time"
           name="appointmentTime"
           min="<?php echo substr($event['eventStartTime'], 0, 5); ?>"
           max="<?php echo substr($event['eventEndTime'], 0, 5); ?>"
           required>
    <br><br>

    <button type="submit" name="book">Confirm Booking</button>
</form>

<?php } else { ?>
    <p>This event is fully booked.</p>
<?php } ?>

<br>

<form action="viewEvents.php">
    <button type="submit">Back</button>
</form>

</body>
</html>
