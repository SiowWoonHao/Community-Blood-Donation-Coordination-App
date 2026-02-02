<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Hospital') {
    header("Location: login.php");
    exit();
}

$notifications = include "processNotifications.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    
    <div style="margin: 50px; text-align: center;">
        <div style="margin-bottom: 30px;">
            <button 
                    onclick="window.location.href='manage_users.php'">
                Manage Users
            </button>
        </div>
        
        <div style="margin-bottom: 30px;">
            <button 
                    onclick="window.location.href='monitor_events.php'">
                Monitor Events
            </button>
        </div>
        
        <div style="margin-bottom: 30px;">
            <button 
                    onclick="window.location.href='approve_events.php'">
                Approve/Reject Events
            </button>
        </div>
        
        <div style="margin-bottom: 30px;">
            <button 
                    onclick="window.location.href='feedback.php'">
                Feedback System
            </button>
        </div>
    </div>
    
    <hr>
    
</body>
</html>
