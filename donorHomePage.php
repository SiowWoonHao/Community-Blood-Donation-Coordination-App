<?php
session_start();
include "db.php";

// login verify
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

// notifications
$notifications = include "processNotifications.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Dashboard</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #c62828, #8e0000);
    display: flex;
    justify-content: center;
    align-items: center;
}

/* MAIN CONTAINER */
.dashboard {
    width: 900px;
    background: white;
    border-radius: 14px;
    padding: 30px 40px 40px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left h1 {
    margin: 0;
    font-size: 26px;
}

.header-left p {
    margin: 6px 0 0;
    font-size: 14px;
    color: #555;
}

.header-right {
    display: flex;
    gap: 12px;
}

.header-right button {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    background: #c62828;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

.header-right button:hover {
    background: #a61b1b;
}

/* CONTENT */
.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 50px;
}

.card {
    background: white;
    border-radius: 20px;
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.35s;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 35px rgba(0,0,0,0.25);
}
</style>

</head>

<body>

<?php
// popup notifications
foreach ($notifications as $msg) {
    echo "<script>alert('$msg');</script>";
}
?>

<div class="dashboard">

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <h1>DONOR DASHBOARD</h1>
            <p><?php echo $_SESSION['userName']; ?></p>
        </div>

        <div class="header-right">
            <button onclick="location.href='donorNotifications.php'">
                Notification
            </button>
            <button onclick="location.href='logout.php'">
                Logout
            </button>
        </div>
    </div>

    <!-- CARDS -->
    <div class="cards">
        <div class="card" onclick="location.href='donorProfile.php'">
            User Profile
        </div>

        <div class="card" onclick="location.href='viewEvents.php'">
            Available Events
        </div>

        <div class="card" onclick="location.href='healthEligibility.php'">
            Health Eligibility
        </div>
    </div>

</div>

</body>
</html>
