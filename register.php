<?php
include "db.php";

if (isset($_POST['register'])) {

    $name    = $_POST['userName'];
    $email   = $_POST['userEmail'];
    $pass    = $_POST['userPassword'];
    $confirm = $_POST['confirmPassword'];
    $phone   = $_POST['userPhone'];

    $role = "Donor";

    // Check password match
    if ($pass != $confirm) {
        echo "<script>alert('Password not match!');
        window.location.href = 'register.php';</script>";
        exit();
    }

    // Check gmail format
    if (!str_ends_with($email, "@gmail.com")) {
        echo "<script>alert('Email must be a Gmail address!');
        window.location.href = 'register.php';</script>";
        exit();
    }

    // Check username or email exists
    $check = mysqli_query(
        $conn,
        "SELECT * FROM user 
         WHERE userName = '$name' 
         OR userEmail = '$email'"
    );

    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username or Email already exists!')
        window.location.href = 'register.php';;</script>";
        exit();
    }

    // Insert user
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<form method="POST">
    Username*:<br>
    <input type="text" name="userName" required><br><br>

    Gmail Address*:<br>
    <input type="email" name="userEmail" required><br><br>

    Password*:<br>
    <input type="password" name="userPassword" required><br><br>

    Confirm Password*:<br>
    <input type="password" name="confirmPassword" required><br><br>

    Phone:<br>
    <input type="text" name="userPhone"><br><br>

    <button type="submit" name="register">Register</button>
</form>

<p><a href="login.php">Back to Login</a></p>

</body>
</html>
