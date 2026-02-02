<?php
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fullName = $_POST['fullName'] ?? '';
    $userEmail = $_POST['userEmail'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $userPassword = $_POST['userPassword'] ?? '';

    if (empty($fullName) || empty($userEmail) || empty($userRole) || empty($userPassword)) {
        $message = "Please fill in all fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

            $checkSql = "SELECT COUNT(*) FROM user WHERE userEmail = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userEmail]);

            if ($checkStmt->fetchColumn() > 0) {
                $message = "Email already exists";
            } else {
                $sql = "INSERT INTO user 
                (userName, userEmail, userPassword, userRole, userPhone, donorAge, donorGender, donorHeight, donorWeight, donorBloodType, eligibilityStatus, organizationName, hospitalAddress)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $fullName, $userEmail, $userPassword, $userRole,
                    '000000000', 0, 'M', 0, 0, 'N/A',
                    'active', '', ''
                ]);

                $message = "User account created successfully!";
                $success = true;
                $_POST = [];
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Create User Account</title>

<style>
body{
    margin:0;
    min-height:100vh;
    font-family:Arial, sans-serif;
    background: linear-gradient(
        -45deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:500% 500%;
    animation: gradientMove 14s ease infinite;
}

@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:1100px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.back-btn{
    border:1px solid #000;
    background:#fff;
    padding:8px 18px;
    cursor:pointer;
}

.form-box{
    border:1px solid #000;
    padding:25px;
    margin-top:20px;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:10px;
    border:1px solid #000;
}

.actions{
    margin-top:25px;
}

.actions button{
    padding:10px 22px;
    border:1px solid #000;
    background:#fff;
    cursor:pointer;
    margin-right:10px;
}
</style>
</head>

<body>

<div class="container">

<h2>Create User Account</h2>

<button class="back-btn" onclick="window.location.href='manage_users.php'">
← Back to Manage Users
</button>

<?php if ($message): ?>
<p style="margin-top:15px;font-weight:bold;color:<?php echo $success?'green':'red'; ?>">
<?php echo $message; ?>
</p>
<?php endif; ?>

<div class="form-box">

<form method="POST">

<div class="form-group">
<label>Full Name *</label>
<input type="text" name="fullName"
value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>" required>
</div>

<div class="form-group">
<label>Username / Email *</label>
<input type="email" name="userEmail"
value="<?php echo htmlspecialchars($_POST['userEmail'] ?? ''); ?>" required>
</div>

<div class="form-group">
<label>Password *</label>
<input type="password" name="userPassword" required>
</div>

<div class="form-group">
<label>Role</label>
<select name="userRole" required>
<option value="donor">Donor</option>
<option value="admin">Admin</option>
<option value="hospital">Hospital</option>
<option value="organizer">Event Organizer</option>
</select>
</div>

<div class="actions">
<button type="submit" name="create_user">Create Account</button>
<button type="button" onclick="window.location.href='manage_users.php'">
Cancel
</button>
</div>

</form>
</div>

</div>

</body>
</html>
