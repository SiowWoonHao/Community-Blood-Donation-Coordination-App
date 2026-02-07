<?php
// Create User Account Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $userName = $_POST['userName'] ?? '';
    $userEmail = $_POST['userEmail'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $userPassword = $_POST['userPassword'] ?? '';
    
    if (empty($userName) || empty($userEmail) || empty($userRole) || empty($userPassword)) {
        $message = "Please fill in all required fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            $checkSql = "SELECT COUNT(*) FROM user WHERE userName = ? OR userEmail = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userName, $userEmail]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Username or Email already exists";
            } else {
                $sql = "INSERT INTO user (userName, userEmail, userPassword, userRole, userPhone, donorAge, donorGender, donorHeight, donorWeight, donorBloodType, userStatus, organizationName, hospitalAddress) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([
                    $userName, $userEmail, $userPassword, $userRole,
                    '000000000', 0, '', 0, 0, 'N/A',
                    'Activate', '', ''
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
/* ===== SAME ADMIN GRADIENT ===== */
body{
    margin:0;
    min-height:100vh;
    font-family: Arial, sans-serif;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:400% 400%;
    animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* ===== CONTAINER ===== */
.container{
    max-width:900px;
    margin:60px auto;
    background:#fff;
    padding:35px 45px;
    border-radius:16px;
    box-shadow:0 15px 30px rgba(0,0,0,0.2);
}

/* ===== BACK BAR ===== */
.top-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:25px;
}
.top-bar a{
    text-decoration:none;
    color:#000;
    font-weight:500;
}

/* ===== FORM ===== */
.form-group{
    margin-bottom:18px;
}

label{
    font-weight:600;
    display:block;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:14px;
}

input:focus, select:focus{
    outline:none;
    border-color:#6a5cff;
}

/* ===== BUTTONS ===== */
.btn{
    padding:10px 22px;
    border-radius:10px;
    border:none;
    background:#6a5cff;
    color:white;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
}

.btn.secondary{
    background:#ddd;
    color:#333;
}

.btn:hover{
    opacity:0.85;
}

/* ===== MESSAGE ===== */
.message{
    font-weight:600;
    margin-bottom:20px;
}

/* ===== FOOT INFO ===== */
.role-info{
    margin-top:30px;
    background:#f9f9ff;
    padding:20px;
    border-radius:12px;
}
</style>
</head>

<body>

<div class="container">

<h2>Create User Account</h2>

<div class="top-bar">
    ← <a href="manageUsers.php">Back to Manage Users</a>
</div>

<?php if ($message): ?>
<p class="message" style="color: <?= $success ? 'green' : 'red'; ?>">
    <?= $message; ?>
</p>
<?php endif; ?>

<form method="POST">

<div class="form-group">
    <label>Full Name *</label>
    <input type="text" name="userName"
           value="<?= htmlspecialchars($_POST['userName'] ?? ''); ?>" required>
</div>

<div class="form-group">
    <label>Username / Email *</label>
    <input type="email" name="userEmail"
           value="<?= htmlspecialchars($_POST['userEmail'] ?? ''); ?>" required>
</div>

<div class="form-group">
    <label>Password *</label>
    <input type="password" name="userPassword" required>
</div>

<div class="form-group">
    <label>Role *</label>
    <select name="userRole" required>
        <option value="">-- Select Role --</option>
        <option value="Admin" <?= ($_POST['userRole'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
        <option value="Donor" <?= ($_POST['userRole'] ?? '') === 'Donor' ? 'selected' : '' ?>>Donor</option>
        <option value="Hospital" <?= ($_POST['userRole'] ?? '') === 'Hospital' ? 'selected' : '' ?>>Hospital</option>
        <option value="Organizer" <?= ($_POST['userRole'] ?? '') === 'Organizer' ? 'selected' : '' ?>>Event Organizer</option>
    </select>
</div>

<br>

<button type="submit" name="create_user" class="btn">Create Account</button>
<button type="button" onclick="window.location.href='manageUsers.php'" class="btn secondary">
    Cancel
</button>

</form>

<div class="role-info">
    <h4>User Role Descriptions</h4>
    <ul>
        <li><strong>Admin</strong> – Full system access, manage users & settings</li>
        <li><strong>Donor</strong> – Register and participate in donation events</li>
        <li><strong>Hospital</strong> – Manage blood inventory</li>
        <li><strong>Event Organizer</strong> – Create and manage events</li>
    </ul>
</div>

</div>

</body>
</html>
