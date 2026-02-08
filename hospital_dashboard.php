<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Hospital') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Dashboard</title>

<style>
*{
    box-sizing:border-box;
    font-family:'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT */
body{
    margin:0;
    min-height:100vh;
    padding:50px 20px;
    background: linear-gradient(
        -45deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:500% 500%;
    animation: gradientMove 16s ease infinite;
}

@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* MAIN CARD */
.dashboard{
    max-width:720px;
    margin:auto;
    background:#fff;
    padding:45px 40px;
    border-radius:20px;
    box-shadow:0 20px 45px rgba(0,0,0,0.22);
    text-align:center;
}

/* TITLE */
.dashboard h1{
    margin-top:0;
    margin-bottom:35px;
}

/* BUTTON GROUP */
.actions{
    display:flex;
    flex-direction:column;
    gap:18px;
    margin-bottom:35px;
}

.dashboard button{
    width:100%;
    padding:16px 28px;
    font-size:16px;
    border-radius:12px;
    border:1px solid #ddd;
    background:#fff;
    cursor:pointer;
    font-weight:600;
    transition:all 0.2s ease;
}

.dashboard button:hover{
    background:#f4f6fb;
    transform:translateY(-1px);
}

/* LOGOUT */
.logout{
    margin-top:30px;
    padding-top:25px;
    border-top:1px solid #eee;
}

.logout button{
    background:#fef2f2;
    border:1px solid #fca5a5;
    color:#b91c1c;
}

.logout button:hover{
    background:#fee2e2;
}
.dashboard-logo {
    height: 50px;
    padding: 6px;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="dashboard">
<div class="header">
    <div class="header-left">
        <img src="logo.png" class="dashboard-logo">

    <h1>Hospital Dashboard</h1>

    <div class="actions">
        <button onclick="window.location.href='view_inventory.php'">
            View Blood Inventory
        </button>

        <button onclick="window.location.href='update_inventory.php'">
            Update Blood Inventory
        </button>

        <button onclick="window.location.href='urgent_request.php'">
            Urgent Blood Request
        </button>

        <button onclick="window.location.href='inventory_report.php'">
            Blood Inventory Report
        </button>
    </div>

    <div class="logout">
        <button onclick="location.href='logout.php'">
            Logout
        </button>
    </div>

</div>

</body>
</html>

