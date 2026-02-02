<?php
session_start();
include "db.php";

// Login & role check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get all notifications
$sqlInbox = "SELECT * FROM notification 
             WHERE userID = '$userID' 
             ORDER BY notificationDate DESC";
$resultInbox = mysqli_query($conn, $sqlInbox);

// Selected notification
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
<title>Appointment Reminder</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT */
body {
    margin: 0;
    min-height: 100vh;
    padding: 40px;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size: 300% 300%;
    animation: gradientMove 18s ease infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* MAIN CARD */
.container {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* HEADER */
.header h2 {
    margin-top: 0;
}

.back {
    border: 1px solid #000;
    padding: 12px;
    margin-bottom: 20px;
}

.back a {
    text-decoration: none;
    color: #000;
    font-weight: 600;
}

/* LAYOUT */
.mail-container {
    display: flex;
    gap: 20px;
}

/* INBOX */
.inbox {
    width: 35%;
    border: 1px solid #000;
    padding: 15px;
}

.mail-item {
    border-bottom: 1px solid #ccc;
    padding: 10px 5px;
}

.mail-item a {
    text-decoration: none;
    color: #000;
    display: block;
}

.mail-item:hover {
    background: #f5f5f5;
}

/* CONTENT */
.content {
    width: 65%;
    border: 1px solid #000;
    padding: 20px;
}

/* OK BUTTON */
.ok-btn {
    margin-top: 20px;
    text-align: right;
}

button {
    padding: 10px 25px;
    border: 1px solid #000;
    background: #fff;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    background: #f0f0f0;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>APPOINTMENT REMINDER</h2>

        <div class="back">
            ← <a href="donorHomePage.php">Back to Dashboard</a>
        </div>
    </div>

    <div class="mail-container">

        <!-- Inbox -->
        <div class="inbox">
            <h3>Inbox</h3>

            <?php if (mysqli_num_rows($resultInbox) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($resultInbox)) { ?>
                    <div class="mail-item">
                        <a href="donorNotifications.php?id=<?php echo $row['notificationID']; ?>">
                            <b><?php echo substr($row['message'], 0, 35); ?>...</b><br>
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
            <?php if ($selectedNotification) { ?>
                <p><b>Date:</b> <?php echo $selectedNotification['notificationDate']; ?></p>
                <hr>
                <p><?php echo nl2br($selectedNotification['message']); ?></p>

                <div class="ok-btn">
                    <a href="donorHomePage.php">
                        <button>OK</button>
                    </a>
                </div>

            <?php } else { ?>
                <p>Select a notification to read.</p>
            <?php } ?>
        </div>

    </div>

</div>

</body>
</html>
