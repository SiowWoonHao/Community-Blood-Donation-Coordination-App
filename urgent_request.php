<?php
// Urgent Blood Request Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_request'])) {
        $bloodType = $_POST['bloodType'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $priority = $_POST['priority'] ?? '3';
        $reason = $_POST['reason'] ?? '';
        $userID = 1; // Assuming userID 1 for requester
        
        if (!empty($bloodType) && is_numeric($quantity) && $quantity > 0 && !empty($reason)) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                
                // Insert request into bloodrequest table
                $sql = "INSERT INTO bloodrequest (userID, quantity, bloodType, priority, reason, requestDate) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $requestDate = date('Y-m-d');
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userID, $quantity, $bloodType, $priority, $reason, $requestDate]);
                
                $success = true;
                
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Please fill all fields: blood type, quantity (> 0), and reason";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Urgent Blood Request</title>
</head>
<body>
    <h2>Urgent Blood Request</h2>
    
    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <form method="POST">
        <label for="bloodType">Select Blood Type:</label><br>
        <select name="bloodType" id="bloodType" required>
            <option value="">-- Select --</option>
            <option value="O+">O+</option>
            <option value="A+">A+</option>
            <option value="B+">B+</option>
            <option value="AB+">AB+</option>
            <option value="O-">O-</option>
            <option value="A-">A-</option>
            <option value="B-">B-</option>
            <option value="AB-">AB-</option>
        </select>
        <br><br>
        
        <label for="quantity">Units needed:</label><br>
        <input type="number" name="quantity" id="quantity" min="1" required>
        <br><br>
        
        <label for="priority">Priority:</label><br>
        <select name="priority" id="priority" required>
            <option value="3">High (3) - Critical</option>
            <option value="2">Medium (2) - Urgent</option>
            <option value="1">Low (1) - Normal</option>
        </select>
        <br><br>
        
        <label for="reason">Reason:</label><br>
        <textarea name="reason" id="reason" rows="4" cols="50" required placeholder="Enter reason for blood request..."></textarea>
        <br><br>
        
        <button type="submit" name="submit_request">Submit Urgent Request</button>
        <button type="button" onclick="window.location.href='inventory.php'">Cancel</button>
    </form>
    
    <hr>
    
    <h3>Recent Blood Requests</h3>
    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $sql = "SELECT * FROM bloodrequest ORDER BY requestDate DESC, requestID DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        $requests = $stmt->fetchAll();
        
        if (empty($requests)) {
            echo "<p>No recent requests found.</p>";
        } else {
            echo "<table border='1' cellpadding='8'>";
            echo "<tr>
                    <th>Blood Type</th>
                    <th>Units</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Reason</th>
                  </tr>";
            
            foreach ($requests as $req) {
                $priorityText = '';
                $priorityColor = '';
                
                switch ($req['priority']) {
                    case 1: $priorityText = 'Low'; $priorityColor = 'green'; break;
                    case 2: $priorityText = 'Medium'; $priorityColor = 'orange'; break;
                    case 3: $priorityText = 'High'; $priorityColor = 'red'; break;
                    default: $priorityText = 'Unknown'; $priorityColor = 'gray';
                }
                
                // Shorten reason if too long
                $shortReason = strlen($req['reason']) > 50 ? substr($req['reason'], 0, 50) . '...' : $req['reason'];
                
                echo "<tr>";
                echo "<td>{$req['bloodType']}</td>";
                echo "<td>{$req['quantity']} units</td>";
                echo "<td style='color: $priorityColor; font-weight: bold;'>$priorityText</td>";
                echo "<td>{$req['requestDate']}</td>";
                echo "<td title=\"" . htmlspecialchars($req['reason']) . "\">$shortReason</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p>Error loading requests: " . $e->getMessage() . "</p>";
    }
    ?>
   
</body>
</html>