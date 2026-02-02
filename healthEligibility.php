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

    if (
        $q1 == "yes" &&
        $q2 == "no" &&
        $q3 == "no" &&
        $q4 == "yes"
    ) {
        $status = "Valid";
    }

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

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT */
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

/* MAIN CARD */
.container {
    max-width: 700px;
    margin: auto;
    background: #fff;
    padding: 30px 35px;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

h2 {
    margin-top: 0;
}

/* QUESTION BLOCK */
.question {
    margin-bottom: 22px;
}

.question p {
    font-weight: 600;
    margin-bottom: 8px;
}

/* BUTTON */
button {
    padding: 10px 22px;
    border-radius: 6px;
    border: 1px solid #000;
    background: #fff;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    background: #f0f0f0;
}

/* BACK LINK */
.back {
    margin-top: 25px;
}
.back a {
    text-decoration: none;
    color: #000;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="container">

<h2>Health Eligibility Check</h2>

<form method="POST">

    <div class="question">
        <p>1. Are you between 18 and 60 years old?</p>
        <input type="radio" name="age" value="yes" required> Yes
        <input type="radio" name="age" value="no"> No
    </div>

    <div class="question">
        <p>2. Have you donated blood in the last 3 months?</p>
        <input type="radio" name="recentDonation" value="yes" required> Yes
        <input type="radio" name="recentDonation" value="no"> No
    </div>

    <div class="question">
        <p>3. Do you currently have any serious illness or infection?</p>
        <input type="radio" name="illness" value="yes" required> Yes
        <input type="radio" name="illness" value="no"> No
    </div>

    <div class="question">
        <p>4. Is your weight above 45kg?</p>
        <input type="radio" name="weight" value="yes" required> Yes
        <input type="radio" name="weight" value="no"> No
    </div>

    <button type="submit" name="submitEligibility">Submit</button>

</form>

<div class="back">
    ← <a href="donorHomePage.php">Back to Home</a>
</div>

</div>

</body>
</html>
