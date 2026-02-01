<?php
// Update Blood Inventory Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $bloodType = $_POST['bloodType'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $userID = 1; // Assuming userID 1 for admin
        
        if (!empty($bloodType) && is_numeric($quantity) && $quantity >= 0) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                
                // Check if record exists
                $checkSql = "SELECT * FROM bloodinventory WHERE bloodType = ? AND userID = ?";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([$bloodType, $userID]);
                
                if ($checkStmt->rowCount() > 0) {
                    // Update existing record
                    $updateSql = "UPDATE bloodinventory SET quantity = ? WHERE bloodType = ? AND userID = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([$quantity, $bloodType, $userID]);
                    $message = "Successfully updated $bloodType to $quantity units";
                    $success = true;
                } else {
                    // Insert new record
                    $insertSql = "INSERT INTO bloodinventory (userID, bloodType, quantity) VALUES (?, ?, ?)";
                    $insertStmt = $pdo->prepare($insertSql);
                    $insertStmt->execute([$userID, $bloodType, $quantity]);
                    $message = "Successfully added $bloodType with $quantity units";
                    $success = true;
                }
                
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Please select blood type and enter valid quantity";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Blood Inventory</title>
</head>
<body>
    <h2>Update Blood Inventory</h2>
    
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
        
        <label for="quantity">Enter New Quantity:</label><br>
        <input type="number" name="quantity" id="quantity" min="0" required>
        <br><br>
        
        <button type="submit" name="update">Update</button>
        <button type="button" onclick="window.location.href='inventory.php'">Cancel</button>
    </form>
    
    <hr>
    
    <h3>Current Inventory</h3>
    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $sql = "SELECT bloodType, SUM(quantity) as total FROM bloodinventory GROUP BY bloodType ORDER BY bloodType";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
        
        if (empty($data)) {
            echo "<p>No inventory data found.</p>";
        } else {
            echo "<table border='1' cellpadding='8'>";
            echo "<tr><th>Type</th><th>Current Units</th></tr>";
            foreach ($data as $row) {
                echo "<tr>";
                echo "<td>{$row['bloodType']}</td>";
                echo "<td>{$row['total']} units</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p>Error loading inventory: " . $e->getMessage() . "</p>";
    }
    ?>
    
</body>
</html>