<?php
session_start();
include "db.php";

// Check login and role
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != "Donor") {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch current donor data
$sql = "SELECT userEmail, userPhone, donorAge, donorGender, donorHeight, 
               donorWeight, donorBloodType
        FROM user
        WHERE userID = '$userID'";

$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if (isset($_POST['updateProfile'])) {

    $password   = $_POST['userPassword'];
    $email      = $_POST['userEmail'];
    $phone      = $_POST['userPhone'];
    $age        = $_POST['donorAge'];
    $gender     = $_POST['donorGender'];
    $height     = $_POST['donorHeight'];
    $weight     = $_POST['donorWeight'];
    $bloodType  = $_POST['donorBloodType'];

    $updateSql = "UPDATE user SET
                    userPassword = '$password',
                    userEmail = '$email',
                    userPhone = '$phone',
                    donorAge = '$age',
                    donorGender = '$gender',
                    donorHeight = '$height',
                    donorWeight = '$weight',
                    donorBloodType = '$bloodType'
                  WHERE userID = '$userID'";

    if (mysqli_query($conn, $updateSql)) {
        echo "<script>
                alert('Profile updated successfully');
                window.location.href = 'donorProfile.php';
              </script>";
    } else {
        echo "<script>alert('Failed to update profile');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Donor Profile</title>

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
.container {
    width: 520px;
    margin: auto;
    background: #fff;
    padding: 35px 30px;
    border-radius: 18px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* FORM */
h2 {
    margin-top: 0;
    margin-bottom: 25px;
    text-align: center;
}

label {
    display: block;
    margin-top: 12px;
    font-weight: 600;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

input:focus, select:focus {
    outline: none;
    border-color: #6a5cff;
}

/* BUTTON */
button {
    margin-top: 20px;
    padding: 10px 18px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
}

button:hover {
    background: #f0f0f0;
}

/* BACK LINK */
.back {
    margin-top: 20px;
    text-align: center;
}

.back a {
    text-decoration: none;
    color: #000;
}
</style>
</head>

<body>

<div class="container">

    <h2>Edit Profile</h2>

    <form method="POST">

        <label>Password</label>
        <input type="password" name="userPassword" required>

        <label>Email</label>
        <input type="email" name="userEmail"
               value="<?php echo $user['userEmail']; ?>" required>

        <label>Phone</label>
        <input type="text" name="userPhone"
               value="<?php echo $user['userPhone']; ?>" required>

        <label>Age</label>
        <input type="number" name="donorAge"
               value="<?php echo $user['donorAge']; ?>" required>

        <label>Gender</label>
        <select name="donorGender" required>
            <option value="M" <?php if ($user['donorGender'] == 'M') echo 'selected'; ?>>Male</option>
            <option value="F" <?php if ($user['donorGender'] == 'F') echo 'selected'; ?>>Female</option>
        </select>

        <label>Height (cm)</label>
        <input type="number" name="donorHeight"
               value="<?php echo $user['donorHeight']; ?>" required>

        <label>Weight (kg)</label>
        <input type="number" name="donorWeight"
               value="<?php echo $user['donorWeight']; ?>" required>

        <label>Blood Type</label>
        <select name="donorBloodType" required>
            <?php
            $types = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"];
            foreach ($types as $type) {
                $selected = ($user['donorBloodType'] == $type) ? "selected" : "";
                echo "<option value='$type' $selected>$type</option>";
            }
            ?>
        </select>

        <button type="submit" name="updateProfile">
            Save Changes
        </button>

    </form>

    <div class="back">
        <a href="donorProfile.php">← Back to Profile</a>
    </div>

</div>

</body>
</html>
