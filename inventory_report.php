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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blood Inventory Report</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME GRADIENT TEMPLATE */
body {
    margin: 0;
    min-height: 100vh;
    padding: 40px;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size: 300% 300%;
    animation: gradientMove 18s ease infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* MAIN CARD */
.card {
    max-width: 800px;
    margin: auto;
    background: #fff;
    padding: 35px 40px;
    border-radius: 18px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* TOP BAR */
.top-bar {
    border: 1px solid #000;
    padding: 12px 16px;
    margin-bottom: 25px;
}

.top-bar a {
    text-decoration: none;
    color: black;
    font-weight: 500;
}

/* FORM */
label {
    font-weight: 600;
}

select, input[type="date"] {
    padding: 8px 10px;
    margin-top: 6px;
    border: 1px solid #000;
}

/* BUTTONS */
button {
    padding: 10px 20px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}

button:hover {
    background: #f0f0f0;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
}
</style>
</head>

<body>

<div class="card">

    <h2>BLOOD INVENTORY REPORT</h2>

    <div class="top-bar">
        <a href="hospital_dashboard.php">← Back to Hospital Profile</a>
    </div>

    <?php if ($message): ?>
        <p style="color: <?php echo $reportSaved ? 'green' : 'red'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Report Type:</label><br>
        <select name="reportType" id="reportType" onchange="toggleReportFields()">
            <option value="inventory">Daily</option>
            <option value="blood_type">By Blood Type</option>
        </select>

        <br><br>

        <div id="bloodTypeSection" style="display:none;">
            <label>Blood Type:</label><br>
            <select name="bloodType">
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
        </div>

        <br>

        <div id="dateRangeSection" style="display:none;">
            <label>Date Range:</label><br>
            <select name="dateRange">
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
        </div>

        <br><br>

        <div style="text-align:right;">
            <button type="submit" name="generate_report">Generate Report</button>
        </div>

    </form>

    <?php if ($reportData): ?>

        <hr>

        <h3><?php echo $reportData['type']; ?></h3>
        <p><b>Period:</b> <?php echo $reportData['period']; ?></p>

        <?php if (isset($reportData['inventory']) && is_array($reportData['inventory'])): ?>
            <table>
                <tr>
                    <th>Blood Type</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>

                <?php foreach ($reportData['inventory'] as $item): ?>
                    <?php
                    if ($item['total'] >= 300) { $status='Good'; $color='green'; }
                    elseif ($item['total'] >= 200) { $status='Moderate'; $color='blue'; }
                    elseif ($item['total'] >= 100) { $status='Low'; $color='orange'; }
                    else { $status='Critical'; $color='red'; }
                    ?>
                    <tr>
                        <td><?php echo $item['bloodType']; ?></td>
                        <td><?php echo $item['total']; ?></td>
                        <td style="color:<?php echo $color; ?>;font-weight:bold;">
                            <?php echo $status; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
function toggleReportFields() {
    let type = document.getElementById('reportType').value;
    document.getElementById('bloodTypeSection').style.display =
        type === 'blood_type' ? 'block' : 'none';
    document.getElementById('dateRangeSection').style.display =
        type === 'blood_type' ? 'block' : 'none';
}
window.onload = toggleReportFields;
</script>

</body>
</html>

