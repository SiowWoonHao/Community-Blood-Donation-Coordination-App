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
        $userID = 1;

        if (!empty($bloodType) && is_numeric($quantity) && $quantity >= 0) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

                $checkSql = "SELECT * FROM bloodinventory WHERE bloodType = ? AND userID = ?";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([$bloodType, $userID]);

                if ($checkStmt->rowCount() > 0) {
                    $updateSql = "UPDATE bloodinventory SET quantity = ? WHERE bloodType = ? AND userID = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([$quantity, $bloodType, $userID]);
                    $message = "Successfully updated $bloodType to $quantity units";
                    $success = true;
                } else {
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

    <h2>UPDATE BLOOD INVENTORY</h2>

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

        <label>Enter New Quantity:</label><br>
        <input type="number" name="quantity" min="0" required>

        <br><br>

        <div style="text-align:right;">
            <button type="submit" name="update">Update</button>
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
