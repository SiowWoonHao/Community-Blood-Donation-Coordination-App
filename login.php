<?php
session_start();
include "db.php";

if (isset($_POST['login'])) {

    $loginInput = $_POST['loginInput'];
    $pass       = $_POST['userPassword'];

    // Login using username or email
    $sql = "SELECT * FROM user
            WHERE (userName = '$loginInput' OR userEmail = '$loginInput')
            AND userPassword = '$pass'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        // Store session data
        $_SESSION['userID']   = $row['userID'];
        $_SESSION['userName'] = $row['userName'];
        $_SESSION['userRole'] = $row['userRole'];

        // Redirect according to role
        if ($row['userRole'] == "Donor") {
            $redirectPage = "donorHomePage.php";
        } elseif ($row['userRole'] == "Hospital") {
            $redirectPage = "hospitalDashboard.php";
        } elseif ($row['userRole'] == "Organizer") {
            $redirectPage = "eventOrganizerDashboard.php";
        } elseif ($row['userRole'] == "Admin") {
            $redirectPage = "adminDashboard.php";
        } else {
            // default fallback
            $redirectPage = "login.php";
        }

        echo "<script>
                alert('Login successful!');
                window.location.href = '$redirectPage';
              </script>";

    } else {
        echo "<script>alert('Invalid login!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<form method="POST">
    Username or Email*:<br>
    <input type="text" name="loginInput" required><br><br>

    Password*:<br>
    <input type="password" name="userPassword" required><br><br>

    <button type="submit" name="login">Login</button>
</form>

<p><a href="register.php">Register Account</a></p>

</body>
</html>
