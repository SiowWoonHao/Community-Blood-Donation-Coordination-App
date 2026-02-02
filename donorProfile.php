<?php
session_start();
include "db.php";

// Safety check
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get user profile
$sqlUser = "SELECT * FROM user WHERE userID = '$userID'";
$resultUser = mysqli_query($conn, $sqlUser);
$user = mysqli_fetch_assoc($resultUser);

// Donation statistics
$sqlStats = "SELECT 
                COUNT(*) AS totalDonations,
                MIN(event.eventDate) AS firstDonation,
                MAX(event.eventDate) AS lastDonation
             FROM appointment
             JOIN event ON appointment.eventID = event.eventID
             WHERE appointment.userID = '$userID'
             AND event.status = 'published'
             AND event.eventDate <= CURDATE()";

$resultStats = mysqli_query($conn, $sqlStats);
$stats = mysqli_fetch_assoc($resultStats);

// Donation history
$sqlHistory = "SELECT appointment.appointmentID, event.eventName, event.eventDate,
                      event.eventStartTime, event.eventEndTime, event.eventVenue,
                      appointment.appointmentTime
               FROM appointment
               JOIN event ON appointment.eventID = event.eventID
               WHERE appointment.userID = '$userID'
               AND event.status = 'published'
               ORDER BY event.eventDate ASC";

$resultHistory = mysqli_query($conn, $sqlHistory);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 GLOBAL ANIMATED GRADIENT */
body {
    margin: 0;
    min-height: 100vh;
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
    padding: 40px;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* MAIN WRAPPER */
.container {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* HEADER */
.header {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 15px;
}

/* BACK */
.back {
    margin-bottom: 25px;
}
.back a {
    text-decoration: none;
    color: #111;
    font-weight: 600;
}

/* GRID */
.grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 25px;
}

/* CARD */
.card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
}

.card h3 {
    margin-top: 0;
    font-size: 16px;
}

/* BUTTONS */
button {
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}

button:hover {
    background: #f0f0f0;
}

/* HISTORY */
.history-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 18px;
}

.history-title {
    text-align: center;
    font-weight: 700;
    margin: 20px 0 10px;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">USER PROFILE</div>

    <div class="back">
        ← <a href="donorHomePage.php">Back to Dashboard</a>
    </div>

    <div class="grid">

        <!-- LEFT COLUMN -->
        <div>

            <div class="card">
                <p><b><?php echo $user['userName']; ?></b></p>
                <p>IC: <?php echo $user['donorIC'] ?? '-'; ?></p>
                <p>Blood Type: <?php echo $user['donorBloodType']; ?></p>
                <p>Phone Num.: <?php echo $user['userPhone']; ?></p>
                <p>Email: <?php echo $user['userEmail']; ?></p>
                <button onclick="location.href='editDonorProfile.php'">Edit Profile</button>
            </div>

            <div class="card">
                <h3>Current Eligibility Status</h3>
                <p><?php echo $user['eligibilityStatus']; ?></p>
                <p>Last updated: <?php echo $user['eligibilityUpdatedDate'] ?? '-'; ?></p>
                <button onclick="location.href='healthEligibility.php'">Edit</button>
            </div>

            <div class="card">
                <h3>Donation Statistics</h3>
                <p>Total Donations: <?php echo $stats['totalDonations'] ?? 0; ?> times</p>
                <p>First Donation: <?php echo $stats['firstDonation'] ?? 'N/A'; ?></p>
                <p>Last Donation: <?php echo $stats['lastDonation'] ?? 'N/A'; ?></p>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div>

            <h3 class="history-title">Donation History</h3>

            <?php if(mysqli_num_rows($resultHistory) > 0) { ?>
                <?php while($row = mysqli_fetch_assoc($resultHistory)) { ?>

                <div class="history-card">
                    <p><b>Event:</b> <?php echo $row['eventName']; ?></p>
                    <p>Date: <?php echo $row['eventDate']; ?></p>
                    <p>
                        Time:
                        <?php echo substr($row['eventStartTime'],0,5); ?>
                        -
                        <?php echo substr($row['eventEndTime'],0,5); ?>
                    </p>
                    <p>Venue: <?php echo $row['eventVenue']; ?></p>

                    <?php
                    $eventEndDateTime = strtotime($row['eventDate'] . ' ' . $row['eventEndTime']);
                    $currentDateTime = time();

                    if ($eventEndDateTime < $currentDateTime) {
                    ?>
                        <button onclick="location.href='donorFeedback.php?appointmentID=<?php echo $row['appointmentID']; ?>'">
                            Feedback
                        </button>
                    <?php } else { ?>
                        <button onclick="location.href='cancelAppointment.php?appointmentID=<?php echo $row['appointmentID']; ?>'">
                            Cancel Booking
                        </button>
                    <?php } ?>
                </div>

                <?php } ?>
            <?php } else { ?>
                <p style="text-align:center;">No donation history.</p>
            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>
