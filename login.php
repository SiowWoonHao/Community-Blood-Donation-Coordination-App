<?php
session_start();
include "db.php";

if (isset($_POST['login'])) {

    $name = $_POST['userName'];
    $pass = $_POST['userPassword'];

    $sql = "SELECT * FROM user 
            WHERE userName = '$name' 
            AND userPassword = '$pass'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['userID'] = $row['userID'];
        $_SESSION['role']  = $row['userRole'];

        echo "<script>
                alert('Login successful!');
                window.location.href = 'donorHomePage.html';
              </script>";
    } else {
        echo "<script>
                alert('Invalid username or password!');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>
<body>

<h2>Login</h2>

<form method="POST">
    Username: <input type="text" name="userName" required><br><br>
    Password: <input type="password" name="userPassword" required><br><br>

    <button type="submit" name="login">Login</button><br><br>
</form>
    <p>Haven't Register Account? <b><i><a href="register.php">Register</a></i></b></p>

</body>
</html>
