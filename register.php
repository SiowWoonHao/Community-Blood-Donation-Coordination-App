<?php
include "db.php";

if (isset($_POST['register'])) {

    $name     = $_POST['userName'];
    $pass     = $_POST['userPassword'];
    $confirm  = $_POST['confirmPassword'];
    $email    = $_POST['userEmail'];   // optional
    $phone    = $_POST['userPhone'];   // optional
    $role     = "Donor";               // default role

    
    // 1. check password match
    if ($pass != $confirm) {
        echo "<script>alert('Password not match!');</script>";
    } else {

        // 2. check username exist
        $check = "SELECT * FROM user WHERE userName = '$name'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('Username already exists!');</script>";
        } else {

            // 3. insert new user
            $sql = "INSERT INTO user 
                    (userName, userPassword, userEmail, userPhone, userRole)
                    VALUES 
                    ('$name', '$pass', '$email', '$phone', '$role')";

            if (mysqli_query($conn, $sql)) {
                echo "<script>
                        alert('Register successful!');
                        window.location.href = 'login.php';
                      </script>";
            } else {
                echo "<script>alert('Register failed!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
</head>
<body>

<h2>Register</h2>

<p>
    ← <a href="login.php">Back to Log In Page</a>
</p>

<h3>Your Profile Information</h3>

<form method="POST">
    Full Name*:
    <input type="text" name="userName" required><br><br>

    Password:
    <input type="password" name="userPassword" required><br><br>

    Confirm Password:
    <input type="password" name="confirmPassword" required><br><br>

    Email (optional):
    <input type="email" name="userEmail"><br><br>

    Phone (optional):
    <input type="text" name="userPhone"><br><br>

    <button type="submit" name="register">Register</button>
</form>

<p>Already Register Account? 
    <b><i><a href="login.php">Login</a></i></b>
</p>

</body>
</html>

