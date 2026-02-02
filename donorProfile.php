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
             AND event.eventDate <= CURDATE()"; // only completed

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
    <title>Donor Profile</title>
</head>
<body>

<h2>Donor Profile</h2>

<!-- User Profile -->
<div style="border:1px solid #000; padding:10px; margin-bottom:20px;">
    <h3>Profile Information</h3>
    <p><b>Name:</b> <?php echo $user['userName']; ?></p>
    <p><b>Email:</b> <?php echo $user['userEmail']; ?></p>
    <p><b>Phone:</b> <?php echo $user['userPhone']; ?></p>
    <p><b>Age:</b> <?php echo $user['donorAge']; ?></p>
    <p><b>Gender:</b> <?php echo $user['donorGender']; ?></p>
    <p><b>Height:</b> <?php echo $user['donorHeight']; ?></p>
    <p><b>Weight:</b> <?php echo $user['donorWeight']; ?></p>
    <p><b>Blood Type:</b> <?php echo $user['donorBloodType']; ?></p>
    <button onclick="location.href='editDonorProfile.php'">Edit</button>
</div>

<!-- Eligibility Status -->
<div style="border:1px solid #000; padding:10px; margin-bottom:20px;">
    <h3>Health Eligibility</h3>
    <p><?php echo $user['eligibilityStatus']; ?></p>
    <button onclick="location.href='healthEligibility.php'">Edit</button>
</div>

<!-- Donation Statistics -->
<div style="border:1px solid #000; padding:10px; margin-bottom:20px;">
    <h3>Donation Statistics</h3>
    <p><b>Total Donations:</b> <?php echo $stats['totalDonations'] ?? 0; ?></p>
    <p><b>First Donation:</b> <?php echo $stats['firstDonation'] ?? 'N/A'; ?></p>
    <p><b>Last Donation:</b> <?php echo $stats['lastDonation'] ?? 'N/A'; ?></p>
</div>

<!-- Donation History -->
<div style="border:1px solid #000; padding:10px; margin-bottom:20px;">
    <h3>Donation History</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Event Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Appointment Time</th>
            <th>Action</th>
        </tr>
        <?php if(mysqli_num_rows($resultHistory) > 0) { ?>
            <?php while($row = mysqli_fetch_assoc($resultHistory)) { ?>
                <tr>
                    <td><?php echo $row['eventName']; ?></td>
                    <td><?php echo $row['eventDate']; ?></td>
                    <td>
                        <?php 
                            echo substr($row['eventStartTime'],0,5) 
                                . " - " . substr($row['eventEndTime'],0,5);
                        ?>
                    </td>
                    <td><?php echo $row['eventVenue']; ?></td>
                    <td><?php echo substr($row['appointmentTime'],0,5); ?></td>
                    <td>
                    <?php
                    $eventEndDateTime = strtotime($row['eventDate'] . ' ' . $row['eventEndTime']);
                    $currentDateTime = time();

                    if ($eventEndDateTime < $currentDateTime) {
                        // Past event → feedback
                    ?>
                        <a href="donorFeedback.php?appointmentID=<?php echo $row['appointmentID']; ?>">
                            <button>Give Feedback</button>
                        </a>
                    <?php
                    } else {
                        // Future event → cancel
                    ?>
                        <a href="cancelAppointment.php?appointmentID=<?php echo $row['appointmentID']; ?>">
                            <button>Cancel Booking</button>
                        </a>
                    <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6" style="text-align:center;">No donation history.</td>
            </tr>
        <?php } ?>
    </table>
</div>

<form action="donorHomePage.php">
    <button type="submit">Back</button>
</form>

<form action="logout.php" method="post">
    <button type="submit">Logout</button>
</form>

</body>
</html>
