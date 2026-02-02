<?php
// viewFeedback.php - View Feedback
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

// Get feedback ID from URL
$feedbackID = $_GET['id'] ?? 0;

if (empty($feedbackID)) {
    die("Feedback ID is required");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Get feedback data
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Feedback</title>
</head>
<body>
    <h1>View Feedback</h1>
    
    <a href="feedback.php">Back to Feedback List</a>
    
    <br><br>
    
    <div>
        <p><strong>From:</strong> <?php echo htmlspecialchars($feedback['userName']); ?> (<?php echo htmlspecialchars($feedback['userEmail']); ?>)</p>
        <p><strong>Submitted:</strong> <?php echo date('F d, Y H:i', strtotime($feedback['createdDate'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($feedback['status']); ?></p>
    </div>
    
    <br>
    
    <div>
        <h3>Message:</h3>
        <p><?php echo htmlspecialchars($feedback['message']); ?></p>
    </div>
    
    <br>
    
    <?php if (!empty($feedback['adminResponse'])): ?>
        <div>
            <h3>Admin Response:</h3>
            <p><?php echo htmlspecialchars($feedback['adminResponse']); ?></p>
            <p><small>Responded on: <?php echo !empty($feedback['respondedDate']) ? date('F d, Y H:i', strtotime($feedback['respondedDate'])) : 'N/A'; ?></small></p>
        </div>
        <br>
    <?php endif; ?>
    
    <div>
        <button onclick="window.location.href='response.php?id=<?php echo $feedback['feedbackID']; ?>'">Response</button>
        <button onclick="window.location.href='feedback.php'">Back to List</button>
    </div>
</body>
</html>