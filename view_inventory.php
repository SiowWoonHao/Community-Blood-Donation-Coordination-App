<?php
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blood Inventory</title>

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
    max-width: 850px;
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

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border: 1px solid #000;
    padding: 10px;
    text-align: center;
}

th {
    background: #f5f5f5;
}
</style>
</head>

<body>

<div class="card">

    <h2>REAL-TIME BLOOD INVENTORY</h2>

    <div class="top-bar">
        <a href="hospital_dashboard.php">← Back to Hospital Dashboard</a>
    </div>

    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        $sql = "SELECT bloodType, SUM(quantity) AS total 
                FROM bloodinventory 
                GROUP BY bloodType 
                ORDER BY bloodType";

        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();

        echo "<table>";
        echo "<tr>
                <th>Blood Type</th>
                <th>Unit(s)</th>
                <th>Status</th>
              </tr>";

        $total = 0;
        $lowStockTypes = [];

        if (empty($data)) {
            echo "<tr><td colspan='3'>No data found in inventory</td></tr>";
        } else {
            foreach ($data as $row) {
                $quantity = (int)$row['total'];
                $total += $quantity;

                if ($quantity >= 300) {
                    $status = "Good";
                    $color = "green";
                } elseif ($quantity >= 200) {
                    $status = "Moderate";
                    $color = "blue";
                } elseif ($quantity >= 100) {
                    $status = "Low";
                    $color = "orange";
                    $lowStockTypes[] = $row['bloodType'];
                } else {
                    $status = "Critical";
                    $color = "red";
                    $lowStockTypes[] = $row['bloodType'];
                }

                echo "<tr>";
                echo "<td><strong>{$row['bloodType']}</strong></td>";
                echo "<td>{$row['total']} units</td>";
                echo "<td style='color:$color; font-weight:bold;'>$status</td>";
                echo "</tr>";
            }
        }

        echo "</table>";

        echo "<p><strong>Total:</strong> $total units</p>";

        if (!empty($lowStockTypes)) {
            echo "<p><strong>Low Stock:</strong> " . implode(", ", $lowStockTypes) . "</p>";
        } else {
            echo "<p><strong>Low Stock:</strong> None</p>";
        }

    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>

</div>

</body>
</html>
