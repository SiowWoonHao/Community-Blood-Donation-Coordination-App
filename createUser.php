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
body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(-45deg, #9ef0e1, #b8e7ff, #c6b8ff, #9ef0e1);
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    background: white;
    width: 90%;
    max-width: 900px;
    margin: 40px auto;
    padding: 35px 45px;
    border-radius: 22px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.back-link {
    color: #6a5cff;
    text-decoration: none;
    font-weight: 500;
}

.form-group {
    margin-bottom: 20px;
}

label {
    font-weight: 500;
    display: block;
    margin-bottom: 6px;
}

input, select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
}

input:focus, select:focus {
    outline: none;
    border-color: #6a5cff;
}

.btn {
    padding: 10px 22px;
    border-radius: 10px;
    border: none;
    background: #6a5cff;
    color: white;
    font-size: 15px;
    cursor: pointer;
}

.btn.secondary {
    background: #ddd;
    color: #333;
}

.btn:hover {
    opacity: 0.85;
}

.message {
    font-weight: bold;
    margin-bottom: 20px;
}
</style>
</head>

<body>
<div class="container">

<h1>Create User Account</h1>
<p><a class="back-link" href="manageUsers.php">← Back to Manage Users</a></p>

<?php if ($message): ?>
<p class="message" style="color: <?= $success ? 'green' : 'red'; ?>">
    <?= $message; ?>
</p>
<?php endif; ?>

<form method="POST">

<div class="form-group">
    <label>Full Name*</label>
    <input type="text" name="userName" 
           value="<?= htmlspecialchars($_POST['userName'] ?? ''); ?>" required>
</div>

<div class="form-group">
    <label>Username / Email*</label>
    <input type="email" name="userEmail" 
           value="<?= htmlspecialchars($_POST['userEmail'] ?? ''); ?>" required>
</div>

<div class="form-group">
    <label>Password*</label>
    <input type="password" name="userPassword" required>
</div>

<div class="form-group">
    <label>Role</label>
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

<hr style="margin:40px 0;">

<h3>User Role Descriptions:</h3>
<ul>
    <li><strong>Admin</strong> - Full system access, can manage all users and settings</li>
    <li><strong>Donor</strong> - Can register for blood donation events</li>
    <li><strong>Hospital</strong> - Can manage blood inventory</li>
    <li><strong>Event Organizer</strong> - Can create and manage events</li>
</ul>

</div>
</body>
</html>