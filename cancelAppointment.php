<?php
session_start();
include "db.php";

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

$sql = "SELECT appointment.*, 
               event.eventName, event.eventDate, event.eventStartTime, 
               event.eventEndTime, event.eventVenue
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

if (isset($_POST['confirmCancel'])) {
    $eventID = $row['eventID'];

    $sqlDelete = "DELETE FROM appointment WHERE appointmentID = '$appointmentID'";
    if (mysqli_query($conn, $sqlDelete)) {

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

if (isset($_POST['keep'])) {
    header("Location: donorProfile.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Appointment</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(
                120deg,
                #f5f7fa,
                #b8f7d4,
                #9be7ff,
                #c7d2fe,
                #fef9c3
            );
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .box {
            border: 2px solid #ccc;
            padding: 20px;
            margin-top: 20px;
        }

        .confirm-box {
            text-align: center;
            margin-top: 30px;
        }

        .confirm-box button {
            padding: 8px 24px;
            margin: 0 10px;
            font-size: 15px;
        }

        a {
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>CANCEL APPOINTMENT</h2>

    <a href="donorProfile.php">← Back to User Profile</a>

    <div class="box">
        <p><b>Event:</b> <?= $row['eventName'] ?></p>
        <p><b>Date:</b> <?= $row['eventDate'] ?></p>
        <p><b>Time:</b>
            <?= substr($row['eventStartTime'],0,5) ?>
            -
            <?= substr($row['eventEndTime'],0,5) ?>
        </p>
        <p><b>Venue:</b> <?= $row['eventVenue'] ?></p>
    </div>

    <div class="box">
        <p><b>Are you sure you want to cancel this appointment?</b></p>
        <p>
            Your appointment will be removed from your record and the time slot
            will become available again.
        </p>

        <form method="POST" class="confirm-box">
            <button type="submit" name="confirmCancel">
                Yes, Cancel Appointment
            </button>

            <button type="submit" name="keep">
                No, Keep Appointment
            </button>
        </form>
    </div>

</div>

</body>
</html>
