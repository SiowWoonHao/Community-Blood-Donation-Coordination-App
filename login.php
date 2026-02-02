<?php
session_start();
include "db.php";

if (isset($_POST['login'])) {

    $loginInput = $_POST['loginInput'];
    $pass       = $_POST['userPassword'];

    $sql = "SELECT * FROM user
            WHERE (userName = '$loginInput' OR userEmail = '$loginInput')
            AND userPassword = '$pass'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        $_SESSION['userID']   = $row['userID'];
        $_SESSION['userName'] = $row['userName'];
        $_SESSION['userRole'] = $row['userRole'];

        if ($row['userRole'] == "Donor") {
            $redirectPage = "donorHomePage.php";
        } elseif ($row['userRole'] == "Hospital") {
            $redirectPage = "hospital_dashboard.php";
        } elseif ($row['userRole'] == "Organizer") {
            $redirectPage = "eventOrganizerDashboard.php";
        } elseif ($row['userRole'] == "Admin") {
            $redirectPage = "admin_dashboard.php";
        } else {
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Blood Donation</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT BACKGROUND */
body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

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
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* LOGIN CARD */
.login-card {
    width: 420px;
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    line-height: 1.3;
}

label {
    font-size: 14px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

input:focus {
    outline: none;
    border-color: #111;
}

/* BUTTONS */
.btn-group {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 10px;
}

button {
    padding: 12px 22px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-login {
    background: #111;
    color: white;
}

.btn-login:hover {
    background: #333;
}

.btn-register {
    background: #f0f0f0;
}

.btn-register:hover {
    background: #e0e0e0;
}
</style>
</head>

<body>

<div class="login-card">

    <h1>
        Community Blood Donation<br>
        Coordination App
    </h1>

    <form method="POST">
        <label>Username / Email *</label>
        <input type="text" name="loginInput" required>

        <label>Password *</label>
        <input type="password" name="userPassword" required>

        <div class="btn-group">
            <button class="btn-login" type="submit" name="login">
                Log In
            </button>

            <a href="register.php">
                <button type="button" class="btn-register">
                    Register
                </button>
            </a>
        </div>
    </form>

</div>

</body>
</html>
