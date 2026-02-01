<?php
// Blood Inventory Report Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$reportData = null;
$message = '';
$reportSaved = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_report'])) {
        $reportType = $_POST['reportType'] ?? 'inventory';
        $dateFrom = $_POST['dateFrom'] ?? '';
        $dateTo = $_POST['dateTo'] ?? '';
        $dateRange = $_POST['dateRange'] ?? '0';
        $userID = 1; // Assuming userID 1
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            if ($reportType === 'inventory') {
                // Current Inventory Report
                $sql = "SELECT bloodType, SUM(quantity) as total 
                        FROM bloodinventory 
                        GROUP BY bloodType 
                        ORDER BY bloodType";
                $stmt = $pdo->query($sql);
                $inventory = $stmt->fetchAll();
                
                $totalUnits = 0;
                $lowStock = [];
                $criticalStock = [];
                $inventoryDetails = [];
                
                foreach ($inventory as $item) {
                    $totalUnits += $item['total'];
                    
                    if ($item['total'] >= 300) {
                        $status = 'Good';
                    } elseif ($item['total'] >= 200) {
                        $status = 'Moderate';
                    } elseif ($item['total'] >= 100) {
                        $status = 'Low';
                        $lowStock[] = $item['bloodType'];
                    } else {
                        $status = 'Critical';
                        $criticalStock[] = $item['bloodType'];
                    }
                    
                    $inventoryDetails[] = [
                        'bloodType' => $item['bloodType'],
                        'quantity' => $item['total'],
                        'status' => $status
                    ];
                }
                
                $summary = "Total Units: $totalUnits | Blood Types: " . count($inventory);
                if (!empty($lowStock)) {
                    $summary .= " | Low Stock: " . implode(', ', $lowStock);
                }
                if (!empty($criticalStock)) {
                    $summary .= " | Critical: " . implode(', ', $criticalStock);
                }
                
                $details = json_encode($inventoryDetails, JSON_PRETTY_PRINT);
                
                // Save to bloodinventoryreport table
                $insertSql = "INSERT INTO bloodinventoryreport 
                              (userID, bloodType, dateRange, generatedDate, summary, details) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                
                $insertStmt = $pdo->prepare($insertSql);
                // For inventory report, bloodType is 'ALL' and dateRange is 0
                $insertStmt->execute([$userID, 'ALL', 0, date('Y-m-d'), $summary, $details]);
                
                $reportData = [
                    'type' => 'Current Inventory Report',
                    'period' => 'As of ' . date('Y-m-d H:i:s'),
                    'inventory' => $inventory,
                    'summary' => [
                        'total_units' => $totalUnits,
                        'blood_types' => count($inventory),
                        'low_stock' => $lowStock,
                        'critical_stock' => $criticalStock
                    ]
                ];
                $reportSaved = true;
                
            } elseif ($reportType === 'blood_type' && !empty($_POST['bloodType'])) {
                // Specific Blood Type Report
                $bloodType = $_POST['bloodType'];
                $dateRange = (int)$_POST['dateRange'];
                
                // Calculate date based on dateRange
                $startDate = date('Y-m-d', strtotime("-$dateRange days"));
                $endDate = date('Y-m-d');
                
                // Get inventory for specific blood type
                $invSql = "SELECT SUM(quantity) as total FROM bloodinventory 
                           WHERE bloodType = ? AND lastUpdated >= ?";
                $invStmt = $pdo->prepare($invSql);
                $invStmt->execute([$bloodType, $startDate]);
                $inventoryResult = $invStmt->fetch();
                
                // Get requests for specific blood type
                $reqSql = "SELECT COUNT(*) as request_count, SUM(quantity) as total_requests 
                           FROM bloodrequest 
                           WHERE bloodType = ? AND requestDate BETWEEN ? AND ?";
                $reqStmt = $pdo->prepare($reqSql);
                $reqStmt->execute([$bloodType, $startDate, $endDate]);
                $requestsResult = $reqStmt->fetch();
                
                $summary = "Blood Type: $bloodType | Period: $dateRange days | ";
                $summary .= "Current Stock: " . ($inventoryResult['total'] ?? 0) . " units | ";
                $summary .= "Requests: " . ($requestsResult['request_count'] ?? 0) . " | ";
                $summary .= "Units Requested: " . ($requestsResult['total_requests'] ?? 0);
                
                $details = json_encode([
                    'inventory' => $inventoryResult,
                    'requests' => $requestsResult,
                    'period' => "$dateRange days ($startDate to $endDate)"
                ], JSON_PRETTY_PRINT);
                
                // Save to bloodinventoryreport table
                $insertSql = "INSERT INTO bloodinventoryreport 
                              (userID, bloodType, dateRange, generatedDate, summary, details) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([$userID, $bloodType, $dateRange, date('Y-m-d'), $summary, $details]);
                
                $reportData = [
                    'type' => "Blood Type Report: $bloodType",
                    'period' => "Last $dateRange days ($startDate to $endDate)",
                    'inventory' => $inventoryResult,
                    'requests' => $requestsResult
                ];
                $reportSaved = true;
                
            } else {
                $message = "Please select blood type and date range";
            }
            
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blood Inventory Report</title>
</head>
<body>
    <h2>Blood Inventory Report</h2>
    
    <?php if ($message): ?>
        <p style="color: <?php echo $reportSaved ? 'green' : 'red'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <form method="POST">
        <label for="reportType">Report Type:</label><br>
        <select name="reportType" id="reportType" onchange="toggleReportFields()">
            <option value="inventory">Current Inventory Summary</option>
            <option value="blood_type">Specific Blood Type Report</option>
        </select>
        <br><br>
        
        <div id="bloodTypeSection" style="display: none;">
            <label for="bloodType">Blood Type:</label><br>
            <select name="bloodType" id="bloodType">
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
        </div>
        
        <div id="dateRangeSection" style="display: none;">
            <label for="dateRange">Date Range (days):</label><br>
            <select name="dateRange" id="dateRange">
                <option value="7">Last 7 Days</option>
                <option value="30" selected>Last 30 Days</option>
                <option value="90">Last 90 Days</option>
                <option value="180">Last 180 Days</option>
            </select>
            <br><br>
        </div>
        
        <button type="submit" name="generate_report">Generate Report</button>
        <button type="button" onclick="window.location.href='inventory.php'">Cancel</button>
    </form>
    
    <hr>
    
    <?php if ($reportData): ?>
        <h3><?php echo $reportData['type']; ?></h3>
        <p><strong>Period:</strong> <?php echo $reportData['period']; ?></p>
        
        <?php if ($reportData['type'] === 'Current Inventory Report' || $reportData['type'] === 'Current Inventory Summary'): ?>
            <!-- Inventory Report -->
            <table border="1" cellpadding="8">
                <tr>
                    <th>Blood Type</th>
                    <th>Quantity (units)</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($reportData['inventory'] as $item): ?>
                    <?php
                    $status = '';
                    $color = '';
                    if ($item['total'] >= 300) {
                        $status = 'Good'; $color = 'green';
                    } elseif ($item['total'] >= 200) {
                        $status = 'Moderate'; $color = 'blue';
                    } elseif ($item['total'] >= 100) {
                        $status = 'Low'; $color = 'orange';
                    } else {
                        $status = 'Critical'; $color = 'red';
                    }
                    ?>
                    <tr>
                        <td><?php echo $item['bloodType']; ?></td>
                        <td><?php echo $item['total']; ?></td>
                        <td style="color: <?php echo $color; ?>; font-weight: bold;">
                            <?php echo $status; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <br>
            <h4>Summary:</h4>
            <p>Total Units: <?php echo $reportData['summary']['total_units']; ?></p>
            <p>Blood Types: <?php echo $reportData['summary']['blood_types']; ?></p>
            <?php if (!empty($reportData['summary']['low_stock'])): ?>
                <p style="color: orange;">Low Stock: <?php echo implode(', ', $reportData['summary']['low_stock']); ?></p>
            <?php endif; ?>
            <?php if (!empty($reportData['summary']['critical_stock'])): ?>
                <p style="color: red;">Critical Stock: <?php echo implode(', ', $reportData['summary']['critical_stock']); ?></p>
            <?php endif; ?>
            
        <?php elseif (strpos($reportData['type'], 'Blood Type Report') !== false): ?>
            <!-- Specific Blood Type Report -->
            <h4>Inventory Status:</h4>
            <p>Current Stock: <?php echo $reportData['inventory']['total'] ?? 0; ?> units</p>
            
            <h4>Request Statistics:</h4>
            <p>Number of Requests: <?php echo $reportData['requests']['request_count'] ?? 0; ?></p>
            <p>Total Units Requested: <?php echo $reportData['requests']['total_requests'] ?? 0; ?></p>
        <?php endif; ?>
        
    <?php endif; ?>
    
    <hr>
    
    <h3>Previous Reports</h3>
    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $sql = "SELECT * FROM bloodinventoryreport ORDER BY generatedDate DESC, reportID DESC LIMIT 5";
        $stmt = $pdo->query($sql);
        $reports = $stmt->fetchAll();
        
        if (empty($reports)) {
            echo "<p>No previous reports found.</p>";
        } else {
            echo "<table border='1' cellpadding='8'>";
            echo "<tr>
                    <th>Report ID</th>
                    <th>Blood Type</th>
                    <th>Date Range</th>
                    <th>Generated Date</th>
                    <th>Summary</th>
                  </tr>";
            
            foreach ($reports as $report) {
                echo "<tr>";
                echo "<td>{$report['reportID']}</td>";
                echo "<td>{$report['bloodType']}</td>";
                echo "<td>" . ($report['dateRange'] == 0 ? 'N/A' : $report['dateRange'] . ' days') . "</td>";
                echo "<td>{$report['generatedDate']}</td>";
                echo "<td title=\"" . htmlspecialchars($report['summary']) . "\">" 
                     . (strlen($report['summary']) > 50 ? substr($report['summary'], 0, 50) . '...' : $report['summary']) 
                     . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p>Error loading previous reports: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <script>
        function toggleReportFields() {
            var reportType = document.getElementById('reportType').value;
            var bloodTypeSection = document.getElementById('bloodTypeSection');
            var dateRangeSection = document.getElementById('dateRangeSection');
            
            if (reportType === 'blood_type') {
                bloodTypeSection.style.display = 'block';
                dateRangeSection.style.display = 'block';
            } else {
                bloodTypeSection.style.display = 'none';
                dateRangeSection.style.display = 'none';
            }
        }
        
        window.onload = function() {
            toggleReportFields();
        };
    </script>
</body>
</html>