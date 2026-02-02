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

/* 🌈 ANIMATED GRADIENT BACKGROUND */
body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

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
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* 🧱 DASHBOARD CONTAINER */
.dashboard {
    width: 900px;
    background: white;
    border-radius: 14px;
    padding: 30px 40px 40px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
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
    margin-top: 6px;
    font-size: 14px;
    color: #555;
}

.header-right {
    display: flex;
    gap: 12px;
}

.header-right button {
    padding: 8px 18px;
    border-radius: 6px;
    border: none;
    background: #111;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

.header-right button:hover {
    background: #333;
}

/* CARDS */
.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 50px;
}

.card {
    height: 180px;
    border-radius: 22px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    transition: 0.35s;
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
            <button onclick="location.href='donorNotifications.php'">Notification</button>
            <button onclick="location.href='logout.php'">Logout</button>
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
