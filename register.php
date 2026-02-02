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
    if (!str_ends_with($email, '@gmail.com')) {
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
        echo "<script>alert('Username or Email already exists!');
        window.location.href = 'register.php';</script>";
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>

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

/* REGISTER CARD */
.register-card {
    width: 720px;
    background: white;
    padding: 35px 45px 45px;
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

/* HEADER */
.register-card h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

.back {
    display: block;
    margin-bottom: 30px;
    font-size: 14px;
}

.back a {
    text-decoration: none;
    color: #111;
    font-weight: 600;
}

.section-title {
    font-weight: 700;
    margin-bottom: 20px;
}

/* FORM */
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
    gap: 15px;
    margin-top: 10px;
}

button {
    padding: 12px 26px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-create {
    background: #111;
    color: white;
}

.btn-create:hover {
    background: #333;
}

.btn-cancel {
    background: #f0f0f0;
}

.btn-cancel:hover {
    background: #e0e0e0;
}
</style>
</head>

<body>

<div class="register-card">

    <h2>Register</h2>

    <div class="back">
        ← <a href="login.php">Back to Log In Page</a>
    </div>

    <div class="section-title">Your Profile Information</div>

    <form method="POST">

        <label>Full Name *</label>
        <input type="text" name="userName" required>

        <label>Email *</label>
        <input type="email" name="userEmail" required>

        <label>Phone Number *</label>
        <input type="text" name="userPhone" required>

        <label>Password *</label>
        <input type="password" name="userPassword" required>

        <label>Confirm Password *</label>
        <input type="password" name="confirmPassword" required>

        <div class="btn-group">
            <button type="submit" name="register" class="btn-create">
                Create
            </button>

            <button type="button" class="btn-cancel"
                onclick="location.href='login.php'">
                Cancel
            </button>
        </div>

    </form>

</div>

</body>
</html>

