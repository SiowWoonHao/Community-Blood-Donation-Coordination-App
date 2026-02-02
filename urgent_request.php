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
        $userID = 1;

        if (!empty($bloodType) && is_numeric($quantity) && $quantity > 0 && !empty($reason)) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

                $sql = "INSERT INTO bloodrequest 
                        (userID, quantity, bloodType, priority, reason, requestDate) 
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Urgent Blood Request</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME GRADIENT */
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

/* CARD */
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

select, input, textarea {
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

    <h2>URGENT BLOOD REQUEST</h2>

    <div class="top-bar">
        <a href="hospital_dashboard.php">← Back to Hospital Dashboard</a>
    </div>

    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Select Blood Type:</label><br>
        <select name="bloodType" required>
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

        <label>Units Needed:</label><br>
        <input type="number" name="quantity" min="1" required>

        <br><br>

        <label>Priority:</label><br>
        <select name="priority" required>
            <option value="3">High (Critical)</option>
            <option value="2">Medium (Urgent)</option>
            <option value="1">Low (Normal)</option>
        </select>

        <br><br>

        <label>Reason:</label><br>
        <textarea name="reason" rows="4" required
                  placeholder="Enter reason for blood request..."></textarea>

        <br><br>

        <div style="text-align:right;">
            <button type="submit" name="submit_request">Submit Request</button>
            <button type="button" onclick="window.location.href='hospital_dashboard.php'">
                Cancel
            </button>
        </div>

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
            echo "<table>";
            echo "<tr>
                    <th>Blood Type</th>
                    <th>Units</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Reason</th>
                  </tr>";

            foreach ($requests as $req) {
                switch ($req['priority']) {
                    case 1: $pText='Low'; $pColor='green'; break;
                    case 2: $pText='Medium'; $pColor='orange'; break;
                    case 3: $pText='High'; $pColor='red'; break;
                    default: $pText='Unknown'; $pColor='gray';
                }

                $shortReason = strlen($req['reason']) > 50
                    ? substr($req['reason'], 0, 50) . '...'
                    : $req['reason'];

                echo "<tr>";
                echo "<td>{$req['bloodType']}</td>";
                echo "<td>{$req['quantity']} units</td>";
                echo "<td style='color:$pColor; font-weight:bold;'>$pText</td>";
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

</div>

</body>
</html>
