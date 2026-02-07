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

$appointmentID = mysqli_real_escape_string($conn, $_GET['appointmentID']);

$sql = "SELECT 
            a.appointmentID,
            a.rating,
            a.comment,
            a.ratingDate,
            a.eventID,
            a.userID,
            u.userName,
            f.feedbackID,
            f.replyComment,
            f.feedbackStatus,
            f.replyDate
        FROM appointment a
        JOIN user u ON a.userID = u.userID
        LEFT JOIN feedback f ON a.appointmentID = f.appointmentID
        WHERE a.appointmentID = '$appointmentID'";

$result = mysqli_query($conn, $sql);
$feedback = mysqli_fetch_assoc($result);

if (!$feedback) {
    echo "<script>alert('Appointment not found'); window.location.href='feedback.php';</script>";
    exit();
}

if (empty($feedback['feedbackID'])) {
    $insertFeedback = "INSERT INTO feedback 
        (userID, appointmentID, eventID, feedbackStatus, replyComment, replyDate)
        VALUES 
        ('{$feedback['userID']}', '$appointmentID', '{$feedback['eventID']}', 'Pending', '', NULL)";
    mysqli_query($conn, $insertFeedback);

    $feedback['feedbackID'] = mysqli_insert_id($conn);
    $feedback['feedbackStatus'] = 'Pending';
    $feedback['replyComment'] = '';
    $feedback['replyDate'] = NULL;
}

if (isset($_POST['submit'])) {
    $replyComment = mysqli_real_escape_string($conn, $_POST['replyComment']);
    $feedbackStatus = mysqli_real_escape_string($conn, $_POST['feedbackStatus']);
    $replyDate = date('Y-m-d H:i:s');

    $updateFeedback = "UPDATE feedback 
        SET replyComment = '$replyComment',
            feedbackStatus = '$feedbackStatus',
            replyDate = '$replyDate'
        WHERE feedbackID = '{$feedback['feedbackID']}'";
    mysqli_query($conn, $updateFeedback);

    $donorID = $feedback['userID'];
    $eventID = $feedback['eventID'];

    $originalComment = $feedback['comment'];

    $message = "Your feedback on {$feedback['ratingDate']} for the event \"{$eventID}\":
\"{$originalComment}\"

Admin replied to your feedback:
\"{$replyComment}\"";

    $message = mysqli_real_escape_string($conn, $message);

    $insertNotification = "INSERT INTO notification 
        (userID, feedbackID, eventID, notificationDate, message)
        VALUES 
        ('$donorID', '{$feedback['feedbackID']}', '$eventID', NOW(), '$message')";
    mysqli_query($conn, $insertNotification);

    echo "<script>
        alert('Feedback replied successfully');
        window.location.href='feedback.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reply Feedback</title>

<style>
*{
    box-sizing:border-box;
    font-family:'Segoe UI', Tahoma, sans-serif;
}

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
    animation: gradientMove 14s ease infinite;
}

@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:720px;
    margin:auto;
    background:#fff;
    padding:35px 40px;
    border-radius:18px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.back{
    margin-bottom:20px;
}

.back a{
    text-decoration:none;
    color:#4f46e5;
    font-weight:600;
}

.back a:hover{
    text-decoration:underline;
}

h2{
    margin-top:0;
    margin-bottom:20px;
}

.info-box{
    background:#f4f6fb;
    border-radius:12px;
    padding:18px 20px;
    margin-bottom:25px;
}

.info-box p{
    margin:8px 0;
}

label{
    display:block;
    margin-top:18px;
    font-weight:600;
}

textarea, select{
    width:100%;
    padding:12px;
    margin-top:6px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}

textarea{
    min-height:120px;
    resize:vertical;
}

button{
    margin-top:25px;
    padding:12px 24px;
    border-radius:10px;
    border:1px solid #4f46e5;
    background:#4f46e5;
    color:white;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    opacity:0.9;
}
</style>
</head>

<body>

<div class="container">

    <div class="back">
        ← <a href="feedback.php">Back to Feedback List</a>
    </div>

    <h2>Reply Feedback</h2>

    <div class="info-box">
        <p><b>Donor:</b> <?= htmlspecialchars($feedback['userName']) ?></p>
        <p><b>Rating:</b> <?= $feedback['rating'] ?> / 5</p>
        <p><b>Comment:</b> <?= htmlspecialchars($feedback['comment']) ?></p>
        <p><b>Rating Date:</b> <?= $feedback['ratingDate'] ?></p>
    </div>

    <form method="POST">
        <label>Reply Comment</label>
        <textarea name="replyComment" required><?= htmlspecialchars($feedback['replyComment']) ?></textarea>

        <label>Feedback Status</label>
        <select name="feedbackStatus" required>
            <option value="Replied" <?= $feedback['feedbackStatus']=='Replied'?'selected':'' ?>>Replied</option>
            <option value="Resolved" <?= $feedback['feedbackStatus']=='Resolved'?'selected':'' ?>>Resolved</option>
        </select>

        <button type="submit" name="submit">Submit Reply</button>
    </form>

</div>

</body>
</html>
