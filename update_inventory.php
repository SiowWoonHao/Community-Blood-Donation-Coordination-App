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
    if (isset($_POST['add']) || isset($_POST['subtract'])) {
        $bloodType = $_POST['bloodType'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $userID = 1;

        if (!empty($bloodType) && is_numeric($quantity) && $quantity > 0) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

                $checkSql = "SELECT quantity FROM bloodinventory WHERE bloodType = ? AND userID = ?";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([$bloodType, $userID]);
                $currentRecord = $checkStmt->fetch();

                if (isset($_POST['add'])) {
                    // ADD operation
                    if ($currentRecord) {
                        $newQuantity = $currentRecord['quantity'] + $quantity;
                        $updateSql = "UPDATE bloodinventory SET quantity = ? WHERE bloodType = ? AND userID = ?";
                        $updateStmt = $pdo->prepare($updateSql);
                        $updateStmt->execute([$newQuantity, $bloodType, $userID]);
                        $message = "Successfully added $quantity units to $bloodType. New total: $newQuantity units";
                    } else {
                        $insertSql = "INSERT INTO bloodinventory (userID, bloodType, quantity) VALUES (?, ?, ?)";
                        $insertStmt = $pdo->prepare($insertSql);
                        $insertStmt->execute([$userID, $bloodType, $quantity]);
                        $message = "Successfully added $bloodType with $quantity units";
                    }
                    $success = true;
                    
                } elseif (isset($_POST['subtract'])) {
                    // SUBTRACT operation
                    if ($currentRecord) {
                        $newQuantity = $currentRecord['quantity'] - $quantity;
                        
                        if ($newQuantity < 0) {
                            $message = "Error: Cannot subtract $quantity units. Current stock is only {$currentRecord['quantity']} units";
                        } else {
                            $updateSql = "UPDATE bloodinventory SET quantity = ? WHERE bloodType = ? AND userID = ?";
                            $updateStmt = $pdo->prepare($updateSql);
                            $updateStmt->execute([$newQuantity, $bloodType, $userID]);
                            $message = "Successfully subtracted $quantity units from $bloodType. New total: $newQuantity units";
                            $success = true;
                        }
                    } else {
                        $message = "Error: $bloodType not found in inventory. Cannot subtract from zero.";
                    }
                }
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Please select blood type and enter valid quantity (greater than 0)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Blood Inventory</title>

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
    max-width: 750px;
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

select, input[type="number"] {
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
    margin-left: 5px;
}

button:hover {
    background: #f0f0f0;
}

button[name="add"] {
    background: #d4edda;
}

button[name="add"]:hover {
    background: #c3e6cb;
}

button[name="subtract"] {
    background: #f8d7da;
}

button[name="subtract"]:hover {
    background: #f5c6cb;
}

.button-group {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
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

    <h2>UPDATE BLOOD INVENTORY</h2>

    <div class="top-bar">
        <a href="hospital_dashboard.php">← Back to Hospital Dashboard</a>
    </div>

    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>; font-weight: 600;">
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

        <label>Enter Quantity:</label><br>
        <input type="number" name="quantity" min="1" required>

        <br><br>

        <div class="button-group">
            <button type="submit" name="add">➕ Add Units</button>
            <button type="submit" name="subtract">➖ Subtract Units</button>
            <button type="button" onclick="window.location.href='hospital_dashboard.php'">
                Cancel
            </button>
        </div>

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
            echo "<table>";
            echo "<tr><th>Blood Type</th><th>Current Units</th></tr>";
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

</div>

</body>
</html>