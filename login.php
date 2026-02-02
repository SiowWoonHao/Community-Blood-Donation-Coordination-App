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
            $redirectPage = "hospitalDashboard.php";
        } elseif ($row['userRole'] == "Organizer") {
            $redirectPage = "eventOrganizerDashboard.php";
        } elseif ($row['userRole'] == "Admin") {
            $redirectPage = "adminDashboard.php";
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
<html>
<head>
    <title>Login | Community Blood Donation</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #c62828, #b71c1c);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-page {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            width: 420px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            animation: fadeIn 0.6s ease;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            line-height: 1.3;
        }

        label {
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #c62828;
            box-shadow: 0 0 0 2px rgba(198,40,40,0.15);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 10px;
        }

        button {
            padding: 12px 22px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-login {
            background: #c62828;
            color: #fff;
        }

        .btn-login:hover {
            background: #a61b1b;
            transform: translateY(-1px);
        }

        .btn-register {
            background: #f2f2f2;
            color: #333;
        }

        .btn-register:hover {
            background: #e0e0e0;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .register-link a {
            color: #c62828;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

<div class="login-page">
    <div class="login-card">

        <h1>
            Community Blood Donation<br>
            Coordination App
        </h1>

        <form method="POST">

            <label>Username / Email address *</label>
            <input type="text" name="loginInput" required>

            <label>Password *</label>
            <input type="password" name="userPassword" required>

            <div class="btn-group">
                <button type="submit" name="login" class="btn-login">Log In</button>
                <a href="register.php">
                    <button type="button" class="btn-register">Register</button>
                </a>
            </div>

        </form>

    </div>
</div>

</body>
</html>

