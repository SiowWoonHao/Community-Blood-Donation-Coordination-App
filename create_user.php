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
    $fullName = $_POST['fullName'] ?? '';
    $userEmail = $_POST['userEmail'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $userPassword = $_POST['userPassword'] ?? '';
    
    // Validate inputs
    if (empty($fullName) || empty($userEmail) || empty($userRole) || empty($userPassword)) {
        $message = "Please fill in all fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            // Check if email already exists
            $checkSql = "SELECT COUNT(*) FROM user WHERE userEmail = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userEmail]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Email already exists";
            } else {
                // Insert new user
                $sql = "INSERT INTO user (userName, userEmail, userPassword, userRole, userPhone, donorAge, donorGender, donorHeight, donorWeight, donorBloodType, eligibilityStatus, organizationName, hospitalAddress) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                
                // Default values for other fields
                $userPhone = '000000000';
                $donorAge = 0;
                $donorGender = 'M';
                $donorHeight = 0;
                $donorWeight = 0;
                $donorBloodType = 'N/A';
                $eligibilityStatus = 'active';
                $organizationName = '';
                $hospitalAddress = '';
                
                $stmt->execute([
                    $fullName, $userEmail, $userPassword, $userRole,
                    $userPhone, $donorAge, $donorGender, $donorHeight,
                    $donorWeight, $donorBloodType, $eligibilityStatus,
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
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>; font-weight: bold;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <div style="margin: 30px 0; max-width: 500px;">
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label for="fullName">Full Name:</label><br>
                <input type="text" id="fullName" name="fullName" 
                       value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userEmail">Username/Email:</label><br>
                <input type="email" id="userEmail" name="userEmail" 
                       value="<?php echo htmlspecialchars($_POST['userEmail'] ?? ''); ?>" 
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
                    <option value="admin" <?php echo ($_POST['userRole'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="donor" <?php echo ($_POST['userRole'] ?? '') === 'donor' ? 'selected' : ''; ?>>Donor</option>
                    <option value="hospital" <?php echo ($_POST['userRole'] ?? '') === 'hospital' ? 'selected' : ''; ?>>Hospital</option>
                    <option value="organizer" <?php echo ($_POST['userRole'] ?? '') === 'organizer' ? 'selected' : ''; ?>>Event Organizer</option>
                </select>
            </div>
            
            <button type="submit" name="create_user" style="padding: 10px 20px; font-size: 16px;">
                Create Account
            </button>
            
            <button type="button" onclick="window.location.href='manage_users.php'" style="padding: 10px 20px; font-size: 16px;">
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
    
    <script>
        // Simple password visibility toggle
        function togglePassword() {
            var passwordField = document.getElementById('userPassword');
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</body>
</html>