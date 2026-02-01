<?php
session_start();
include "db.php";

// Check login and role
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Donor") {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch donor profile
$sql = "SELECT userName, userEmail, userPhone, donorAge, donorGender, 
               donorHeight, donorWeight, donorBloodType, eligibilityStatus
        FROM user
        WHERE userID = '$userID'";

$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 700px;
            margin: auto;
        }
        .box {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .box h3 {
            margin-top: 0;
        }
        button {
            padding: 8px 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>My Profile</h2>

    <!-- Box 1: User Profile -->
    <div class="box">
        <h3>User Profile</h3>

        <p><strong>Name:</strong> <?php echo $user['userName']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['userEmail']; ?></p>
        <p><strong>Phone:</strong> <?php echo $user['userPhone']; ?></p>
        <p><strong>Age:</strong> <?php echo $user['donorAge']; ?></p>
        <p><strong>Gender:</strong> <?php echo $user['donorGender']; ?></p>
        <p><strong>Height (cm):</strong> <?php echo $user['donorHeight']; ?></p>
        <p><strong>Weight (kg):</strong> <?php echo $user['donorWeight']; ?></p>
        <p><strong>Blood Type:</strong> <?php echo $user['donorBloodType']; ?></p>

        <br>
        <button onclick="location.href='editDonorProfile.php'">
            Edit Profile
        </button>
    </div>

    <!-- Box 2: Health Eligibility -->
    <div class="box">
        <h3>Health Eligibility Status</h3>

        <p>
            <strong>Status:</strong>
            <?php echo $user['eligibilityStatus']; ?>
        </p>

        <br>
        <button onclick="location.href='healthEligibility.php'">
            Check / Edit Eligibility
        </button>
    </div>

    <a href="donorHomePage.php">← Back to Home</a>

</div>

</body>
</html>
