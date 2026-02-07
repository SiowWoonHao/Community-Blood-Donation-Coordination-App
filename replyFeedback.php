<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['appointmentID'])) {
    header("Location: feedback.php");
    exit();
}

$appointmentID = $_GET['appointmentID'];

$sql = "SELECT a.appointmentID, a.rating, a.comment, a.ratingDate, a.eventID, a.userID, 
               u.userName, f.feedbackID, f.replyComment, f.feedbackStatus, f.replyDate
        FROM appointment a
        JOIN user u ON a.userID = u.userID
        LEFT JOIN feedback f ON a.appointmentID = f.appointmentID
        WHERE a.appointmentID='$appointmentID'";
$result = mysqli_query($conn, $sql);
$feedback = mysqli_fetch_assoc($result);

if (!$feedback) {
    echo "<script>alert('Appointment not found.'); window.location.href='feedback.php';</script>";
    exit();
}

if (!$feedback['feedbackID']) {
    $insert = "INSERT INTO feedback (userID, appointmentID, eventID, feedbackStatus, replyComment, replyDate)
               VALUES ('{$feedback['userID']}', '$appointmentID', '{$feedback['eventID']}', 'Pending', '', NULL)";
    mysqli_query($conn, $insert);
    $feedback['feedbackID'] = mysqli_insert_id($conn);
    $feedback['feedbackStatus'] = 'Pending';
    $feedback['replyComment'] = '';
    $feedback['replyDate'] = NULL;
}

if (isset($_POST['submit'])) {
    $replyComment = mysqli_real_escape_string($conn, $_POST['replyComment']);
    $feedbackStatus = $_POST['feedbackStatus'];
    $replyDate = date('Y-m-d H:i:s');

    $update = "UPDATE feedback 
               SET replyComment='$replyComment', feedbackStatus='$feedbackStatus', replyDate='$replyDate'
               WHERE feedbackID='{$feedback['feedbackID']}'";
    mysqli_query($conn, $update);

    $donorID = $feedback['userID'];
    $eventID = $feedback['eventID'];
    $message = mysqli_real_escape_string($conn, "Admin replied to your feedback: '$replyComment'");

    $insertNotification = "INSERT INTO notification (userID, feedbackID, eventID, notificationDate, message)
                           VALUES ('$donorID', '{$feedback['feedbackID']}', '$eventID', CURDATE(), '$message')";
    mysqli_query($conn, $insertNotification);

    echo "<script>
            alert('Feedback replied successfully!');
            window.location.href='feedback.php';
          </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reply Feedback</title>

<style>
/* ===== SAME ADMIN GRADIENT ===== */
body{
    margin:0;
    min-height:100vh;
    font-family: Arial, sans-serif;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:400% 400%;
    animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* ===== CONTAINER ===== */
.container{
    max-width:700px;
    margin:60px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 15px 30px rgba(0,0,0,0.2);
}

/* ===== BACK BAR ===== */
.back{
    border:2px solid #ccc;
    padding:10px;
    margin-bottom:20px;
}
.back a{
    text-decoration:none;
    color:#000;
    font-weight:500;
}

label{
    display:block;
    margin-top:15px;
    font-weight:600;
}

textarea, select{
    width:100%;
    padding:10px;
    margin-top:5px;
    font-size:14px;
}

button{
    padding:10px 20px;
    margin-top:20px;
    cursor:pointer;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

    <div class="back">
        ← <a href="feedback.php">Back to Feedback</a>
    </div>

    <h2>Reply Feedback</h2>

    <p><b>Donor:</b> <?= htmlspecialchars($feedback['userName']) ?></p>
    <p><b>Rating:</b> <?= $feedback['rating'] ?> / 5</p>
    <p><b>Comment:</b> <?= htmlspecialchars($feedback['comment']) ?></p>
    <p><b>Rating Date:</b> <?= $feedback['ratingDate'] ?></p>

    <form method="POST">

        <label for="replyComment">Reply Comment</label>
        <textarea name="replyComment" id="replyComment" required><?= htmlspecialchars($feedback['replyComment'] ?? '') ?></textarea>

        <label for="feedbackStatus">Feedback Status</label>
        <select name="feedbackStatus" id="feedbackStatus" required>
            <option value="Replied" <?= $feedback['feedbackStatus']=='Replied'?'selected':'' ?>>Replied</option>
            <option value="Resolved" <?= $feedback['feedbackStatus']=='Resolved'?'selected':'' ?>>Resolved</option>
        </select>

        <button type="submit" name="submit">Submit Reply</button>

    </form>

</div>

</body>
</html>

