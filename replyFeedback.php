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

    $eventName = mysqli_real_escape_string($conn, $feedback['eventID']);

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
* { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
body { margin:0; padding:40px; background:#f5f7fa; }
.container {
    max-width:700px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:16px;
    box-shadow:0 15px 30px rgba(0,0,0,0.2);
}
label { display:block; margin-top:15px; font-weight:600; }
textarea, select { width:100%; padding:10px; margin-top:5px; }
button { padding:10px 20px; margin-top:20px; cursor:pointer; font-weight:600; }
.back { margin-bottom:20px; }
.back a { text-decoration:none; color:#000; }
</style>
</head>
<body>

<div class="container">
    <div class="back">
        ← <a href="feedback.php">Back to Feedback List</a>
    </div>

    <h2>Reply Feedback</h2>

    <p><b>Donor:</b> <?= htmlspecialchars($feedback['userName']) ?></p>
    <p><b>Rating:</b> <?= $feedback['rating'] ?> / 5</p>
    <p><b>Comment:</b> <?= htmlspecialchars($feedback['comment']) ?></p>
    <p><b>Rating Date:</b> <?= $feedback['ratingDate'] ?></p>

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
