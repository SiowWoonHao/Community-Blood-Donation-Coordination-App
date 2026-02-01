<?php
// Simple HTML output for testing
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $sql = "SELECT bloodType, SUM(quantity) as total FROM bloodinventory GROUP BY bloodType ORDER BY bloodType";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();
    
    echo "<a href='hospital_dashboard.php' 
    style='display:inline-block; 
           margin-bottom:15px; 
           text-decoration:none; 
           border:1px solid black; 
           padding:6px 12px; 
           color:black;'>
    ← Back to Hospital Dashboard
    </a>";
    echo "<h3>Real-Time Blood Inventory</h3>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Type</th><th>Unit(s)</th><th>Status</th></tr>";
    
    $total = 0;
    $lowStockTypes = [];
    
    if (empty($data)) {
        echo "<tr><td colspan='3'>No data found in inventory</td></tr>";
    } else {
        foreach ($data as $row) {
            $quantity = (int)$row['total'];
            $total += $quantity;
            
            // Determine status based on criteria
            if ($quantity >= 300) {
                $status = "Good";
                $statusColor = "green";
            } elseif ($quantity >= 200) {
                $status = "Moderate";
                $statusColor = "blue";
            } elseif ($quantity >= 100) {
                $status = "Low";
                $statusColor = "orange";
                $lowStockTypes[] = $row['bloodType'];
            } else {
                $status = "Critical";
                $statusColor = "red";
                $lowStockTypes[] = $row['bloodType'];
            }
            
            echo "<tr>";
            echo "<td><strong>{$row['bloodType']}</strong></td>";
            echo "<td>{$row['total']} units</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>$status</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "<p><strong>Total: $total units</strong></p>";
    
    // Display low stock types
    if (!empty($lowStockTypes)) {
        echo "<p><strong>Low Stock:</strong> " . implode(", ", $lowStockTypes) . "</p>";
    } else {
        echo "<p><strong>Low Stock:</strong> None</p>";
    }
    
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
