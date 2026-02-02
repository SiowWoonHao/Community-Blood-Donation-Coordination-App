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
    
    // Validate inputs
    if (empty($userName) || empty($userEmail) || empty($userRole) || empty($userPassword)) {
        $message = "Please fill in all required fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            // Check if username or email already exists
            $checkSql = "SELECT COUNT(*) FROM user WHERE userName = ? OR userEmail = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userName, $userEmail]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Username or Email already exists";
            } else {
                // Insert new user
                $sql = "INSERT INTO user (userName, userEmail, userPassword, userRole, userPhone, donorAge, donorGender, donorHeight, donorWeight, donorBloodType, userStatus, organizationName, hospitalAddress) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                
                // Default values for other fields
                $userPhone = '000000000';
                $donorAge = 0;
                $donorGender = '';
                $donorHeight = 0;
                $donorWeight = 0;
                $donorBloodType = 'N/A';
                $userStatus = 'Activate'; // default to Activate
                $organizationName = '';
                $hospitalAddress = '';
                
                $stmt->execute([
                    $userName, $userEmail, $userPassword, $userRole,
                    $userPhone, $donorAge, $donorGender, $donorHeight,
                    $donorWeight, $donorBloodType, $userStatus,
                    $organizationName, $hospitalAddress
                ]);
                
                $message = "User account created successfully!";
                $success = true;
                
                // Clear form on success
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
</head>
<body>
    <h1>Create User Account</h1>
    
    <?php if ($message): ?>
        <p style="color: <?= $success ? 'green' : 'red'; ?>; font-weight: bold;">
            <?= $message; ?>
        </p>
    <?php endif; ?>
    
    <div style="margin: 30px 0; max-width: 500px;">
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label for="userName">Username:</label><br>
                <input type="text" id="userName" name="userName" 
                       value="<?= htmlspecialchars($_POST['userName'] ?? ''); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userEmail">Email:</label><br>
                <input type="email" id="userEmail" name="userEmail" 
                       value="<?= htmlspecialchars($_POST['userEmail'] ?? ''); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userPassword">Password:</label><br>
                <input type="password" id="userPassword" name="userPassword" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="userRole">Role:</label><br>
                <select id="userRole" name="userRole" style="width: 320px; padding: 8px;" required>
                    <option value="">-- Select Role --</option>
                    <option value="Admin" <?= ($_POST['userRole'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Donor" <?= ($_POST['userRole'] ?? '') === 'Donor' ? 'selected' : '' ?>>Donor</option>
                    <option value="Hospital" <?= ($_POST['userRole'] ?? '') === 'Hospital' ? 'selected' : '' ?>>Hospital</option>
                    <option value="Organizer" <?= ($_POST['userRole'] ?? '') === 'Organizer' ? 'selected' : '' ?>>Event Organizer</option>
                </select>
            </div>
            
            <button type="submit" name="create_user" style="padding: 10px 20px; font-size: 16px;">
                Create Account
            </button>
            
            <button type="button" onclick="window.location.href='manageUsers.php'" style="padding: 10px 20px; font-size: 16px;">
                Cancel
            </button>
        </form>
    </div>
    
    <hr>
    
    <h3>User Role Descriptions:</h3>
    <ul>
        <li><strong>Admin</strong> - Full system access, can manage all users and settings</li>
        <li><strong>Donor</strong> - Can register for blood donation events, view personal info</li>
        <li><strong>Hospital</strong> - Can manage blood inventory, submit requests, view reports</li>
        <li><strong>Event Organizer</strong> - Can create and manage blood donation events</li>
    </ul>
</body>
</html>
