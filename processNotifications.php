<?php
// processNotifications.php
// This file only inserts notifications if needed

if (!isset($_SESSION)) { session_start(); }
include "db.php";

$userID = $_SESSION['userID'];
$today = date('Y-m-d');

// Get all future events booked by donor
$sql = "SELECT a.appointmentID, e.eventID, e.eventName, e.eventDate
        FROM appointment a
        JOIN event e ON a.eventID = e.eventID
        WHERE a.userID = '$userID'";
$result = mysqli_query($conn, $sql);

// To store messages for popup
$notificationsToShow = [];

while ($row = mysqli_fetch_assoc($result)) {
    $eventDate = $row['eventDate'];
    $notifyDate = date('Y-m-d', strtotime("$eventDate -1 day"));

    if ($notifyDate == $today) {
        // Check if notification already exists
        $check = "SELECT * FROM notification 
                  WHERE userID='$userID' AND eventID='".$row['eventID']."' 
                  AND notificationDate='$today'";
        $resCheck = mysqli_query($conn, $check);

        if (mysqli_num_rows($resCheck) == 0) {
            // Insert notification
            $message = "Reminder: Your event ".$row['eventName']." is tomorrow!";
            $message = mysqli_real_escape_string($conn, $message);

            $insert = "INSERT INTO notification (userID, eventID, notificationDate, message)
                    VALUES ('$userID', '".$row['eventID']."', '$today', '$message')";
            mysqli_query($conn, $insert);


            // Store message for popup
            $notificationsToShow[] = $message;
        }
    }
}

// Return the array of new notifications
return $notificationsToShow;
