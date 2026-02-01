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
        echo "<script>
                alert('Login successful!');
                window.location.href = 'donorHomePage.html';
              </script>";
    } else {
        echo "<script>alert('Invalid username or password!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">

  <h1>Community Blood Donation<br>Coordination App</h1>

  <form method="POST">

    <label>Username / email address*</label><br>
    <input type="text" name="userName" required><br><br>

    <label>Password*</label><br>
    <input type="password" name="userPassword" required><br><br>

    <button type="submit" name="login">Log In</button>
    <button type="button" onclick="location.href='register.php'">
      Register
    </button>

  </form>

  <p>
    Haven't Register Account?
    <b><i><a href="register.php">Register</a></i></b>
  </p>

</div>

</body>
</html>
