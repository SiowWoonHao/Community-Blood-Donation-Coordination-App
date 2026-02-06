<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

$sqlCheck = "SELECT eligibilityStatus FROM user WHERE userID = '$userID'";
$resultCheck = mysqli_query($conn, $sqlCheck);
$rowCheck = mysqli_fetch_assoc($resultCheck);

if ($rowCheck['eligibilityStatus'] != 'Valid') {
    echo "<script>
        alert('You are not eligible to donate blood. Please complete health eligibility check.');
        window.location.href = 'healthEligibility.php';
    </script>";
    exit();
}

$search = "";
$sql = "SELECT * FROM event
        WHERE status = 'published'
        AND eventDate >= CURDATE()";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql .= " AND eventName LIKE '%$search%'";
}

$sql .= " ORDER BY eventDate ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Events</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(
                120deg,
                #f5f7fa,
                #b8f7d4,
                #9be7ff,
                #c7d2fe,
                #fef9c3
            );
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-bar {
            width: 60%;
        }

        .search-bar input {
            width: 100%;
            padding: 8px;
        }

        .event-card {
            border: 2px solid #ccc;
            padding: 20px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .event-info p {
            margin: 4px 0;
        }

        .event-actions {
            text-align: right;
        }

        .event-actions button {
            display: block;
            margin-bottom: 8px;
            padding: 6px 16px;
        }

        .availability {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .pagination {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>AVAILABLE EVENTS</h2>

    <a href="donorHomePage.php">← Back to Dashboard</a>

    <br><br>

    <div class="top-bar">
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search……"
                   value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="event-card">
                <div class="event-info">
                    <p><strong>Event: <?= $row['eventName'] ?></strong></p>
                    <p>Date: <?= $row['eventDate'] ?></p>
                    <p>
                        Time:
                        <?= substr($row['eventStartTime'], 0, 5) ?>
                        -
                        <?= substr($row['eventEndTime'], 0, 5) ?>
                    </p>
                    <p>Venue: <?= $row['eventVenue'] ?></p>
                </div>

                <div class="event-actions">
                    <div class="availability">
                        Available:
                        <?= $row['availableSlots'] ?>/<?= $row['maxSlots'] ?>
                    </div>

                    <?php if ($row['availableSlots'] > 0) { ?>
                        <button onclick="window.location.href='bookAppointment.php?eventID=<?= $row['eventID'] ?>'">
                            Book Now
                        </button>
                    <?php } else { ?>
                        <button disabled>Full</button>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>No events match your search.</p>
    <?php } ?>

    <div class="pagination">
        ‹ 1 2 › <br>
        showing available events
    </div>

</div>

</body>
</html>

