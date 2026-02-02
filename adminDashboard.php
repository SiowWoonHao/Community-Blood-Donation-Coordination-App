<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Admin') {
    header("Location: login.php");
    exit();
}

$notifications = include "processNotifications.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME GRADIENT AS DONOR */
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

/* 🧱 MAIN WHITE CONTAINER (SAME SIZE FEEL AS DONOR) */
.dashboard-wrapper {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    border-radius: 26px;
    padding: 40px 50px 60px;
    box-shadow: 0 30px 60px rgba(0,0,0,0.25);
}

/* HEADER */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 50px;
}

.dashboard-header h1 {
    margin: 0;
    font-size: 34px;
}

/* LOGOUT */
.logout-btn {
    padding: 10px 22px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}

.logout-btn:hover {
    background: #f2f2f2;
}

/* 🟦 CARD GRID (JUST NICE SIZE, NOT TOO BIG) */
.card-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
}

/* 🧊 EACH CARD */
.card {
    height: 190px;
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 18px 35px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 28px 50px rgba(0,0,0,0.25);
}
</style>
</head>

<body>

<div class="dashboard-wrapper">

    <!-- HEADER -->
    <div class="dashboard-header">
        <h1>ADMIN DASHBOARD</h1>
        <button class="logout-btn" onclick="window.location.href='logout.php'">
            Logout
        </button>
    </div>

    <!-- CARDS -->
    <div class="card-grid">

        <div class="card" onclick="window.location.href='manageUsers.php'">
            Manage User
        </div>

        <div class="card" onclick="window.location.href='monitorEvents.php'">
            Monitor Event
        </div>

        <div class="card" onclick="window.location.href='approveEvents.php'">
            Approve / Reject Event
        </div>

        <div class="card" onclick="window.location.href='feedback.php'">
            Feedback System
        </div>

    </div>

</div>

</body>
</html>
