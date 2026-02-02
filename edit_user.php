<?php
// Edit User Account Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$success = false;
$user = null;

// Get user ID from URL
$userID = $_GET['id'] ?? 0;

if (empty($userID)) {
    die("User ID is required");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Get user data
    $sql = "SELECT * FROM user WHERE userID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("User not found");
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $fullName = $_POST['fullName'] ?? '';
    $userEmail = $_POST['userEmail'] ?? '';
    $userPhone = $_POST['userPhone'] ?? '';
    $userRole = $_POST['userRole'] ?? '';
    $donorBloodType = $_POST['donorBloodType'] ?? '';
    $eligibilityStatus = $_POST['eligibilityStatus'] ?? '';
    
    // Validate inputs
    if (empty($fullName) || empty($userEmail) || empty($userRole)) {
        $message = "Please fill in required fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            // Check if email already exists (excluding current user)
            $checkSql = "SELECT COUNT(*) FROM user WHERE userEmail = ? AND userID != ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userEmail, $userID]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Email already exists for another user";
            } else {
                // Update user
                $updateSql = "UPDATE user SET 
                            userName = ?, 
                            userEmail = ?, 
                            userPhone = ?, 
                            userRole = ?, 
                            donorBloodType = ?, 
                            eligibilityStatus = ?
                            WHERE userID = ?";
                
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    $fullName, $userEmail, $userPhone, $userRole,
                    $donorBloodType, $eligibilityStatus, $userID
                ]);
                
                // Refresh user data
                $stmt->execute([$userID]);
                $user = $stmt->fetch();
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
    <title>Edit User Account</title>
</head>
<body>
    <h1>Edit User Account</h1>
    
    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>; font-weight: bold;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <?php if ($user): ?>
    <div style="margin: 30px 0; max-width: 500px;">
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label for="fullName">Full Name:</label><br>
                <input type="text" id="fullName" name="fullName" 
                       value="<?php echo htmlspecialchars($user['userName']); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userEmail">Username/Email:</label><br>
                <input type="email" id="userEmail" name="userEmail" 
                       value="<?php echo htmlspecialchars($user['userEmail']); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userPhone">Phone Number:</label><br>
                <input type="text" id="userPhone" name="userPhone" 
                       value="<?php echo htmlspecialchars($user['userPhone']); ?>" 
                       style="width: 300px; padding: 8px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="userRole">Role:</label><br>
                <select id="userRole" name="userRole" style="width: 320px; padding: 8px;" required>
                    <option value="admin" <?php echo $user['userRole'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="donor" <?php echo $user['userRole'] === 'donor' ? 'selected' : ''; ?>>Donor</option>
                    <option value="hospital" <?php echo $user['userRole'] === 'hospital' ? 'selected' : ''; ?>>Hospital</option>
                    <option value="organizer" <?php echo $user['userRole'] === 'organizer' ? 'selected' : ''; ?>>Event Organizer</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="donorBloodType">Blood Type:</label><br>
                <select id="donorBloodType" name="donorBloodType" style="width: 320px; padding: 8px;">
                    <option value="N/A" <?php echo $user['donorBloodType'] === 'N/A' ? 'selected' : ''; ?>>Not Applicable</option>
                    <option value="O+" <?php echo $user['donorBloodType'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                    <option value="A+" <?php echo $user['donorBloodType'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                    <option value="B+" <?php echo $user['donorBloodType'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                    <option value="AB+" <?php echo $user['donorBloodType'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                    <option value="O-" <?php echo $user['donorBloodType'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                    <option value="A-" <?php echo $user['donorBloodType'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                    <option value="B-" <?php echo $user['donorBloodType'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                    <option value="AB-" <?php echo $user['donorBloodType'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="eligibilityStatus">Status:</label><br>
                <select id="eligibilityStatus" name="eligibilityStatus" style="width: 320px; padding: 8px;" required>
                    <option value="active" <?php echo $user['eligibilityStatus'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="eligible" <?php echo $user['eligibilityStatus'] === 'eligible' ? 'selected' : ''; ?>>Eligible</option>
                    <option value="pending" <?php echo $user['eligibilityStatus'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="inactive" <?php echo $user['eligibilityStatus'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" name="update_user">
                Update Account
            </button>
            
            <button type="button" onclick="window.location.href='manage_users.php'">
                Cancel
            </button>
        </form>
    </div>
    
    <hr>
    
    <h3>User Information:</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>User ID</th>
            <td><?php echo $user['userID']; ?></td>
        </tr>
        <tr>
            <th>Age</th>
            <td><?php echo $user['donorAge']; ?></td>
        </tr>
        <tr>
            <th>Gender</th>
            <td><?php echo $user['donorGender']; ?></td>
        </tr>
        <tr>
            <th>Height</th>
            <td><?php echo $user['donorHeight']; ?> cm</td>
        </tr>
        <tr>
            <th>Weight</th>
            <td><?php echo $user['donorWeight']; ?> kg</td>
        </tr>
        <tr>
            <th>Organization</th>
            <td><?php echo htmlspecialchars($user['organizationName']); ?></td>
        </tr>
        <tr>
            <th>Hospital Address</th>
            <td><?php echo htmlspecialchars($user['hospitalAddress']); ?></td>
        </tr>
    </table>
    
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>

</body>
</html>