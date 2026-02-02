<?php
// Deactivate Account Page
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

// Handle deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_deactivate'])) {
        try {
            // Update status to 'inactive'
            $sql = "UPDATE user SET eligibilityStatus = 'inactive' WHERE userID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userID]);
            
            $message = "Account deactivated successfully!";
            $success = true;
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM user WHERE userID = ?");
            $stmt->execute([$userID]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['cancel'])) {
        header("Location: manage_users.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deactivate Account</title>
</head>
<body>
    <h1>Deactivate Account</h1>
    
    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>; font-weight: bold; padding: 10px; background: #f0f0f0;">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <?php if ($user): ?>
        <?php if ($user['eligibilityStatus'] === 'inactive'): ?>
            <div>
                <h2>Account Already Inactive</h2>
                <p>User: <strong><?php echo htmlspecialchars($user['userName']); ?></strong></p>
                <p>Email: <strong><?php echo htmlspecialchars($user['userEmail']); ?></strong></p>
                <p>Status: <strong style="color: red;">INACTIVE</strong></p>
                <p>This account is already deactivated.</p>
                <br>
                <button onclick="window.location.href='manage_users.php'" style="padding: 10px 20px; font-size: 16px;">
                    Back to Users
                </button>
            </div>
        <?php else: ?>
            <div>
                <h2>Confirm Deactivation</h2>
                <p>Are you sure you want to deactivate this account?</p>
                
                <div>
                    <p><strong>User Information:</strong></p>
                    <p>Name: <?php echo htmlspecialchars($user['userName']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($user['userEmail']); ?></p>
                    <p>Role: <?php echo ucfirst($user['userRole']); ?></p>
                    <p>Current Status: <strong><?php echo ucfirst($user['eligibilityStatus']); ?></strong></p>
                </div>
                
                <p><strong>Warning:</strong> Deactivated accounts cannot login to the system.</p>
                
                <form method="POST" style="margin-top: 20px;">
                    <button type="submit" name="confirm_deactivate">
                        Deactivate Account
                    </button>
                    
                    <button type="submit" name="cancel">                       
                         Cancel
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>
</body>
</html>