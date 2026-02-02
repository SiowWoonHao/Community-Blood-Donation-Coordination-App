<?php
// response.php - Response to Feedback
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;
$feedback = null;

$feedbackID = $_GET['id'] ?? 0;
if (empty($feedbackID)) {
    die("Feedback ID is required");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    $sql = "SELECT * FROM feedback WHERE feedbackID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$feedbackID]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        die("Feedback not found");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminResponse = $_POST['adminResponse'] ?? '';
    $status = $_POST['status'] ?? 'replied';

    if (!empty($adminResponse)) {
        $updateSql = "UPDATE feedback SET 
                        adminResponse = ?,
                        status = ?,
                        respondedDate = NOW()
                      WHERE feedbackID = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$adminResponse, $status, $feedbackID]);

        $message = "Response sent successfully!";
    } else {
        $message = "Please enter a response message";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Respond to Feedback</title>

<style>
body{
    margin:0;
    min-height:100vh;
    font-family:Arial, sans-serif;
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
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:35px 40px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.section{
    border-bottom:1px solid #000;
    padding-bottom:15px;
    margin-bottom:20px;
}

.box{
    border:1px solid #000;
    padding:15px;
    margin-top:10px;
}

textarea{
    width:100%;
    height:120px;
    border:1px solid #000;
    padding:10px;
    resize:none;
}

.buttons{
    margin-top:20px;
}

button{
    padding:8px 16px;
    border:1px solid #000;
    background:#fff;
    cursor:pointer;
    margin-right:10px;
}

.note{
    margin-top:15px;
    font-size:14px;
}
</style>
</head>

<body>

<div class="container">

<h2>Response</h2>

<div class="section">
    <p>
        <strong>Name</strong>
        (<?php echo htmlspecialchars($feedback['userEmail']); ?>)
    </p>

    <div class="box">
        <?php echo htmlspecialchars($feedback['message']); ?>
    </div>

    <p>
        Submitted <?php echo date('M d, Y', strtotime($feedback['createdDate'])); ?>
    </p>
</div>

<h3>Admin Response</h3>

<form method="POST">

    <div class="box">
        <textarea name="adminResponse" placeholder="Type your response..."><?php
            echo htmlspecialchars($_POST['adminResponse'] ?? '');
        ?></textarea>
    </div>

    <div class="buttons">
        <button type="submit" name="status" value="resolved">Mark as Resolved</button>
        <button type="submit" name="status" value="replied">Send Reply</button>
    </div>

</form>

<div class="note">
    Note : mark event resolved will close the feedback without sending a reply<br>
    Note : send reply will send a response to the user and mark the feedback as replied.
</div>

<br>

<button onclick="window.location.href='viewFeedback.php'">Close</button>

</div>

</body>
</html>
