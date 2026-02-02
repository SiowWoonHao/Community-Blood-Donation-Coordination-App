<?php
// feedback.php - Main Feedback List Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$feedbacks = [];
$searchTerm = '';
$filter = 'all';

// Handle search and filter
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? 'all';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        $sql = "SELECT * FROM feedback WHERE 1=1";
        $params = [];
        
        if (!empty($searchTerm)) {
            $sql .= " AND (userName LIKE ? OR message LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }
        
        if ($filter !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $filter;
        }
        
        $sql .= " ORDER BY createdDate DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $feedbacks = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback</title>
</head>
<body>
    <h1>Feedback</h1>
    
    <a href="admin_dashboard.php">Back to Dashboard</a>
    
    <br><br>
    
    <div>
        <form method="GET" style="display: inline; margin-left: 20px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="search feedback">
            <button type="submit">Search</button>
            <?php if (!empty($searchTerm) || $filter !== 'all'): ?>
                <button type="button" onclick="window.location.href='feedback.php'">Reset</button>
            <?php endif; ?>
        </form>
    </div>
    
    <br><br>
    
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>donor</th>
                <th>submitted</th>
                <th>status</th>
                <th>actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($feedbacks)): ?>
                <tr>
                    <td colspan="4" align="center">No feedback found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['userName']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($fb['createdDate'])); ?></td>
                        <td><?php echo $fb['status']; ?></td>
                        <td>
                            <button onclick="window.location.href='viewFeedback.php?id=<?php echo $fb['feedbackID']; ?>'">view</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <br>
    <p>Total: <?php echo count($feedbacks); ?> feedback entries</p>
</body>
</html>