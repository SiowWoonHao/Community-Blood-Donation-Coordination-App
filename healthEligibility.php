<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Donor") {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (isset($_POST['submitEligibility'])) {

    $q1 = $_POST['age'];
    $q2 = $_POST['recentDonation'];
    $q3 = $_POST['illness'];
    $q4 = $_POST['weight'];

    // default invalid status
    $status = "Invalid";

    // all condition should satisfied
    if (
        $q1 == "yes" &&
        $q2 == "no" &&
        $q3 == "no" &&
        $q4 == "yes"
    ) {
        $status = "Valid";
    }

    // update eligibilityStatus
    $sql = "UPDATE user 
            SET eligibilityStatus = '$status' 
            WHERE userID = '$userID'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Health eligibility status: $status');
                window.location.href = 'donorHomePage.php';
              </script>";
    } else {
        echo "<script>alert('Failed to update eligibility status');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health Eligibility Check</title>
</head>
<body>

<h2>Health Eligibility Check</h2>

<form method="POST">

    <p>1. Are you between 18 and 60 years old?</p>
    <input type="radio" name="age" value="yes" required> Yes
    <input type="radio" name="age" value="no"> No

    <p>2. Have you donated blood in the last 3 months?</p>
    <input type="radio" name="recentDonation" value="yes" required> Yes
    <input type="radio" name="recentDonation" value="no"> No

    <p>3. Do you currently have any serious illness or infection?</p>
    <input type="radio" name="illness" value="yes" required> Yes
    <input type="radio" name="illness" value="no"> No

    <p>4. Is your weight above 45kg?</p>
    <input type="radio" name="weight" value="yes" required> Yes
    <input type="radio" name="weight" value="no"> No

    <br><br>

    <button type="submit" name="submitEligibility">
        Submit
    </button>

</form>

<br>
<a href="donorHomePage.php">← Back to Home</a>

</body>
</html>
