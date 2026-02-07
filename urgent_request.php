<?php
session_start();
include "db.php";

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {

    $bloodType = $_POST['bloodType'] ?? '';
    $quantity  = $_POST['quantity'] ?? '';
    $priority  = $_POST['priority'] ?? '3';
    $reason    = $_POST['reason'] ?? '';
    $userID    = $_SESSION['userID'] ?? 1;

    if ($bloodType !== '' && is_numeric($quantity) && $quantity > 0 && $reason !== '') {

        $bloodTypeEscaped = mysqli_real_escape_string($conn, $bloodType);
        $reasonEscaped    = mysqli_real_escape_string($conn, $reason);
        $requestDate      = date('Y-m-d');

        $insertRequest = "
            INSERT INTO bloodrequest
            (userID, quantity, bloodType, priority, reason, requestDate)
            VALUES
            ('$userID', '$quantity', '$bloodTypeEscaped', '$priority', '$reasonEscaped', '$requestDate')
        ";

        mysqli_query($conn, $insertRequest);

        if ($bloodType === 'ALL') {

            $donorSql = "
                SELECT userID
                FROM user
                WHERE userRole = 'Donor'
            ";

            $notifMessage = mysqli_real_escape_string(
                $conn,
                "Urgent blood request (ALL blood types): $quantity units needed. Reason: $reason"
            );

        } else {

            $donorSql = "
                SELECT userID
                FROM user
                WHERE userRole = 'Donor'
                AND donorBloodType = '$bloodTypeEscaped'
            ";

            $notifMessage = mysqli_real_escape_string(
                $conn,
                "Urgent blood request for $bloodType: $quantity units needed. Reason: $reason"
            );
        }

        $donorResult = mysqli_query($conn, $donorSql);

        while ($donor = mysqli_fetch_assoc($donorResult)) {

            $donorID = $donor['userID'];

            $insertNotification = "
                INSERT INTO notification
                (userID, eventID, feedbackID, notificationDate, message)
                VALUES
                ('$donorID', NULL, NULL, NOW(), '$notifMessage')
            ";

            mysqli_query($conn, $insertNotification);
        }

        $success = true;
        $message = "Urgent blood request submitted successfully.";

    } else {
        $message = "Please fill in all required fields correctly.";
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
.card {
    max-width: 800px;
    margin: auto;
    background: #fff;
    padding: 35px 40px;
    border-radius: 18px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}
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
label {
    font-weight: 600;
}
select, input, textarea {
    padding: 8px 10px;
    margin-top: 6px;
    border: 1px solid #000;
}
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
        <p style="color: <?= $success ? 'green' : 'red'; ?>">
            <?= $message; ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Select Blood Type:</label><br>
        <select name="bloodType" required>
            <option value="">-- Select --</option>
            <option value="ALL">ALL</option>
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
            <option value="3">High</option>
            <option value="2">Medium</option>
            <option value="1">Low</option>
        </select>

        <br><br>

        <label>Reason:</label><br>
        <textarea name="reason" rows="4" required></textarea>

        <br><br>

        <div style="text-align:right;">
            <button type="submit" name="submit_request">Submit Request</button>
            <button type="button" onclick="window.location.href='hospital_dashboard.php'">Cancel</button>
        </div>

    </form>

    <hr>

    <h3>Recent Blood Requests</h3>

    <?php
    $recentSql = "
        SELECT *
        FROM bloodrequest
        ORDER BY requestDate DESC, requestID DESC
        LIMIT 10
    ";
    $recentResult = mysqli_query($conn, $recentSql);

    if (mysqli_num_rows($recentResult) === 0) {
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

        while ($row = mysqli_fetch_assoc($recentResult)) {

            if ($row['priority'] == 3) {
                $pText = 'High';
            } elseif ($row['priority'] == 2) {
                $pText = 'Medium';
            } else {
                $pText = 'Low';
            }

            echo "<tr>";
            echo "<td>{$row['bloodType']}</td>";
            echo "<td>{$row['quantity']}</td>";
            echo "<td>$pText</td>";
            echo "<td>{$row['requestDate']}</td>";
            echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
    ?>

</div>

</body>
</html>
