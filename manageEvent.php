<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== "Organizer") {
    echo "<script>alert('Access denied'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['eventID'])) {
    echo "<script>alert('No event selected'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$eventID = $_GET['eventID'];
$userID  = $_SESSION['userID'];

$sql = "SELECT * FROM event WHERE eventID = '$eventID' AND userID = '$userID'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) !== 1) {
    echo "<script>alert('Event not found'); window.location.href='eventOrganizerDashboard.php';</script>";
    exit();
}

$event = mysqli_fetch_assoc($result);

if (isset($_POST['updateStatus'])) {

    $newStatus = $_POST['status'];
    $oldStatus = $event['status'];

    $update = "
        UPDATE event 
        SET status = '$newStatus'
        WHERE eventID = '$eventID' AND userID = '$userID'
    ";

    if (mysqli_query($conn, $update)) {

        if ($oldStatus !== 'Cancelled' && $newStatus === 'Cancelled') {

            $eventNameEscaped = mysqli_real_escape_string($conn, $event['eventName']);

            $donorSql = "
                SELECT DISTINCT userID
                FROM appointment
                WHERE eventID = '$eventID'
            ";

            $donorResult = mysqli_query($conn, $donorSql);

            while ($donor = mysqli_fetch_assoc($donorResult)) {

                $donorID = $donor['userID'];

                $message = mysqli_real_escape_string(
                    $conn,
                    "Your appointment for event \"$eventNameEscaped\" has been cancelled by the organizer."
                );

                $insertNotification = "
                    INSERT INTO notification
                    (userID, eventID, feedbackID, notificationDate, message)
                    VALUES
                    ('$donorID', '$eventID', NULL, NOW(), '$message')
                ";

                mysqli_query($conn, $insertNotification);
            }
        }

        echo "<script>
                alert('Event status updated successfully');
                window.location.href='eventOrganizerDashboard.php';
              </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Event Status</title>

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

        .page-container {
            max-width: 900px;
            margin: 60px auto;
            background: white;
            border-radius: 14px;
            padding: 35px 45px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        h2 {
            margin-top: 0;
        }

        .status-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 15px 0 30px;
        }

        .radio-group label {
            display: block;
            margin: 10px 0;
            font-size: 16px;
        }

        .warning-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 20px 0;
        }

        .button-row {
            margin-top: 25px;
        }

        button {
            padding: 8px 18px;
            border: 1px solid #000;
            background: #fff;
            cursor: pointer;
            margin-right: 12px;
        }

        button:hover {
            background: #eee;
        }

        a {
            text-decoration: none;
            color: black;
        }
    </style>
</head>

<body>

<div class="page-container">

    <h2>Manage Event Status</h2>

    <p><strong>Event:</strong> <?= htmlspecialchars($event['eventName']); ?></p>

    <div class="status-box">
        <strong>Current Status:</strong> <?= $event['status']; ?>
    </div>

    <form method="POST">

        <p><strong>Change Status To:</strong></p>

        <div class="radio-group">
            <label>
                <input type="radio" name="status" value="Published"
                    <?= $event['status'] === "Published" ? "checked" : ""; ?>>
                Publish
            </label>

            <label>
                <input type="radio" name="status" value="Cancelled"
                    <?= $event['status'] === "Cancelled" ? "checked" : ""; ?>>
                Cancel Event
            </label>
        </div>

        <div class="warning-box">
            <strong>Warning:</strong>
            Cancelling this event will notify all registered donors.
        </div>

        <div class="button-row">
            <button type="submit" name="updateStatus">Confirm</button>
            <a href="eventOrganizerDashboard.php">
                <button type="button">Back</button>
            </a>
        </div>

    </form>

</div>

</body>
</html>
