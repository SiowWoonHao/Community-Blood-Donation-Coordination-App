<?php
// response.php - Response to Feedback
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;
$feedback = null;

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminResponse = $_POST['adminResponse'] ?? '';
    $status = $_POST['status'] ?? 'replied';
    
    if (!empty($adminResponse)) {
        try {
            // Update feedback with response
            $updateSql = "UPDATE feedback SET 
                         adminResponse = ?,
                         status = ?,
                         respondedDate = NOW()
                         WHERE feedbackID = ?";
            
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$adminResponse, $status, $feedbackID]);
            
            $message = "Response sent successfully!";
            $success = true;
            
            // Refresh feedback data
            $stmt->execute([$feedbackID]);
            $feedback = $stmt->fetch();
            
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Please enter a response message";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Respond to Feedback</title>
</head>
<body>
    <h1>Respond to Feedback</h1>
    
    <a href="viewFeedback.php?id=<?php echo $feedbackID; ?>">Back to Feedback</a>
    
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    
    <?php if ($feedback): ?>
    <br>
    
    <div>
        <p><strong>From:</strong> <?php echo htmlspecialchars($feedback['userName']); ?> (<?php echo htmlspecialchars($feedback['userEmail']); ?>)</p>
        <p><strong>Submitted:</strong> <?php echo date('F d, Y H:i', strtotime($feedback['createdDate'])); ?></p>
        <p><strong>Current Status:</strong> <?php echo ucfirst($feedback['status']); ?></p>
    </div>
    
    <br>
    
    <div>
        <h3>Original Message:</h3>
        <p><?php echo htmlspecialchars($feedback['message']); ?></p>
    </div>
    
    <br>
    
    <form method="POST">
        <div>
            <label><strong>Admin Response:</strong></label><br>
            <textarea name="adminResponse" rows="6" cols="60" placeholder="Type your response here..."><?php echo htmlspecialchars($_POST['adminResponse'] ?? ''); ?></textarea>
        </div>
        
        <br>
        
        <div>
            <label><strong>Mark as:</strong></label>
            <select name="status">
                <option value="replied" selected>Replied</option>
                <option value="resolved">Resolved</option>
                <option value="pending">Keep as Pending</option>
            </select>
        </div>
        
        <br>
        
        <button type="submit" name="send_response">Send Reply</button>
        <button type="button" onclick="window.location.href='viewFeedback.php?id=<?php echo $feedbackID; ?>'">Cancel</button>
    </form>
    <?php else: ?>
        <p>Feedback not found.</p>
    <?php endif; ?>
</body>
</html>