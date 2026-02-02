<?php
session_start();
include "db.php";

// Login & role check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (!isset($_GET['appointmentID'])) {
    header("Location: donorProfile.php");
    exit();
}

$appointmentID = $_GET['appointmentID'];

$sql = "SELECT a.*, e.eventName, e.eventDate, e.eventVenue, e.eventEndTime
        FROM appointment a
        JOIN event e ON a.eventID = e.eventID
        WHERE a.appointmentID='$appointmentID' AND a.userID='$userID'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: donorProfile.php");
    exit();
}

$eventEndDateTime = strtotime($data['eventDate'] . ' ' . $data['eventEndTime']);
if ($eventEndDateTime > time()) {
    header("Location: donorProfile.php");
    exit();
}

if ($data['rating'] > 0) {
    echo "<script>
            alert('You have already submitted feedback.');
            window.location.href='donorProfile.php';
          </script>";
    exit();
}

if (isset($_POST['submit'])) {
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $update = "UPDATE appointment 
               SET rating='$rating', comment='$comment'
               WHERE appointmentID='$appointmentID'";
    mysqli_query($conn, $update);

    echo "<script>
            alert('Thank you for your feedback!');
            window.location.href='donorProfile.php';
          </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Share Your Feedback</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT */
body {
    margin: 0;
    min-height: 100vh;
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
    padding: 40px;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* MAIN CARD */
.container {
    max-width: 900px;
    margin: auto;
    background: #fff;
    padding: 30px 35px;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* HEADER */
.header {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.back {
    border: 1px solid #000;
    padding: 12px;
    width: 100%;
}

.back a {
    text-decoration: none;
    color: #000;
    font-weight: 600;
}

/* FEEDBACK BOX */
.feedback-box {
    border: 1px solid #000;
    padding: 20px;
    margin-top: 20px;
}

.feedback-box h3 {
    margin-top: 0;
}

/* FORM */
textarea {
    width: 100%;
    height: 120px;
    padding: 10px;
    resize: none;
}

select {
    padding: 6px;
    margin-top: 5px;
}

/* BUTTONS */
.actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

button {
    padding: 10px 22px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}

button:hover {
    background: #f0f0f0;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>SHARE YOUR FEEDBACK</h2>

        <div class="back">
            ← <a href="donorProfile.php">Back to Dashboard</a>
        </div>
    </div>

    <div class="feedback-box">
        <h3>How was your donation experience?</h3>

        <form method="POST">

            <p><b>Rate your experience:</b></p>
            <select name="rating" required>
                <option value="">-- Select --</option>
                <option value="1">★ Very Bad</option>
                <option value="2">★★ Bad</option>
                <option value="3">★★★ Average</option>
                <option value="4">★★★★ Good</option>
                <option value="5">★★★★★ Excellent</option>
            </select>

            <p><b>Your Comment:</b></p>
            <textarea name="comment" placeholder="Share details of your experience..." required></textarea>

            <div class="actions">
                <a href="donorProfile.php">
                    <button type="button">SKIP</button>
                </a>

                <button type="submit" name="submit">SUBMIT</button>
            </div>

        </form>
    </div>

</div>

</body>
</html>

