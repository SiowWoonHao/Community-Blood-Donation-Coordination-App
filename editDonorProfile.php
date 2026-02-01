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

    // Build update query
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
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select {
            width: 100%;
            padding: 6px;
        }
        button {
            margin-top: 15px;
            padding: 8px 15px;
            cursor: pointer;
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

    <br>
    <a href="donorProfile.php">← Back to Profile</a>

</div>

</body>
</html>