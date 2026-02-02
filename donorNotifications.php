<?php
session_start();
include "db.php";

// Login & role check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get all notifications for this donor
$sqlInbox = "SELECT * FROM notification 
             WHERE userID = '$userID' 
             ORDER BY notificationDate DESC";
$resultInbox = mysqli_query($conn, $sqlInbox);

// If user clicks a notification
$selectedNotification = null;
if (isset($_GET['id'])) {
    $nid = $_GET['id'];
    $sqlDetail = "SELECT * FROM notification 
                  WHERE notificationID='$nid' AND userID='$userID'";
    $resultDetail = mysqli_query($conn, $sqlDetail);
    $selectedNotification = mysqli_fetch_assoc($resultDetail);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { display: flex; width: 100%; }
        .inbox { width: 30%; border-right: 1px solid #ccc; padding: 10px; }
        .content { width: 70%; padding: 10px; }
        .mail-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .mail-item a {
            text-decoration: none;
            color: black;
        }
        .mail-item:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Notifications</h2>
<p><a href="donorHomePage.php">← Back to Home</a></p>

<div class="container">

    <!-- Inbox -->
    <div class="inbox">
        <h3>Inbox</h3>

        <?php if (mysqli_num_rows($resultInbox) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($resultInbox)) { ?>
                <div class="mail-item">
                    <a href="donorNotifications.php?id=<?php echo $row['notificationID']; ?>">
                        <b><?php echo substr($row['message'], 0, 30); ?>...</b><br>
                        <small><?php echo $row['notificationDate']; ?></small>
                    </a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No notifications.</p>
        <?php } ?>
    </div>

    <!-- Content -->
    <div class="content">
        <h3>Message</h3>

        <?php if ($selectedNotification) { ?>
            <p><b>Date:</b> <?php echo $selectedNotification['notificationDate']; ?></p>
            <p><b>Message:</b></p>
            <p><?php echo $selectedNotification['message']; ?></p>
        <?php } else { ?>
            <p>Select a notification to read.</p>
        <?php } ?>
    </div>

</div>

</body>
</html>
