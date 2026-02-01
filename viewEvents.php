<?php
session_start();
include "db.php";

// Safety check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

// Check donor eligibility status
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

// Base SQL
$sql = "SELECT * FROM event
        WHERE status = 'published'
        AND eventDate >= CURDATE()";

// Search by event name
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql .= " AND eventName LIKE '%$search%'";
}

$sql .= " ORDER BY eventDate ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Events</title>
</head>
<body>

<h2>Available Blood Donation Events</h2>

<!-- Search bar -->
<form method="GET">
    <input type="text" name="search" placeholder="Search event name"
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<br>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Event Name</th>
        <th>Date</th>
        <th>Time</th>
        <th>Venue</th>
        <th>Slots</th>
        <th>Action</th>
    </tr>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['eventName']; ?></td>
                <td><?php echo $row['eventDate']; ?></td>
                <td>
                    <?php
                        echo substr($row['eventStartTime'], 0, 5)
                        . " - " .
                        substr($row['eventEndTime'], 0, 5);
                    ?>
                </td>
                <td><?php echo $row['eventVenue']; ?></td>
                <td>
                    <?php
                        echo $row['availableSlots']
                        . " / " .
                        $row['maxSlots'];
                    ?>
                </td>
                <td>
                    <?php if ($row['availableSlots'] > 0) { ?>
                        <a href="bookAppointment.php?eventID=<?php echo $row['eventID']; ?>">
                            <button>Book Now</button>
                        </a>
                    <?php } else { ?>
                        <button disabled>Full</button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="6" style="text-align:center;">
                No events match your search.
            </td>
        </tr>
    <?php } ?>
</table>

<br>

<form action="donorHomePage.php">
    <button type="submit">Back</button>
</form>

</body>
</html>
