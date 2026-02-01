<?php
session_start();
include "db.php";

// login verify
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

// Include the notification processing
$notifications = include "processNotifications.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Home Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 500px;
            margin: auto;
            text-align: center;
        }
        button {
            width: 250px;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            cursor: pointer;
        }
        .logout {
            background-color: #f44336;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<div class="container">
    <?php
        // Show popup alerts if there are new notifications
        foreach ($notifications as $msg) {
            echo "<script>alert('$msg');</script>";
        }
    ?>
    <h2>Welcome, <?php echo $_SESSION['userName']; ?> ❤️</h2>
    <p>Role: <?php echo $_SESSION['userRole']; ?></p>

    <hr>

    <button onclick="location.href='donorNotifications.php'">
        🔔 Notifications
    </button>

    <button onclick="location.href='donorProfile.php'">
        👤 User Profile
    </button>

    <button onclick="location.href='viewEvents.php'">
        📅 Available Events
    </button>

    <button onclick="location.href='healthEligibility.php'">
        🩺 Health Eligibility
    </button>

    <hr>

    <button class="logout" onclick="location.href='logout.php'">
        Logout
    </button>

</div>

</body>
</html>
