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
    <title>Deactivate User Account</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;

            background: linear-gradient(
                120deg,
                #f5f7fa,
                #b8f7d4,
                #9be7ff,
                #c7d2fe,
                #fef9c3
            );
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .page-container {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .back-link {
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .warning-box {
            border: 2px solid #ccc;
            padding: 25px;
            margin-top: 20px;
        }

        .buttons {
            margin-top: 30px;
        }

        button {
            padding: 8px 20px;
            margin-right: 15px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="page-container">

    <h2>Deactivate User Account</h2>

    <!-- Back link -->
    <a class="back-link" href="edit_user.php?id=<?= $userID ?>">
        ← Back to Edit User Account
    </a>

    <div class="warning-box">

        <p>
            ❌ <strong>Are you sure to deactivate this user account?</strong>
        </p>

        <p>
            The user Name (<?= htmlspecialchars($user['userEmail']) ?>)
            will be deactivated and lose access to the system.
            You can reactivate this account at any time.
        </p>

        <form method="POST" class="buttons">
            <button type="submit" name="confirm_deactivate">
                Deactivate
            </button>

            <button type="button"
                onclick="window.location.href='edit_user.php?id=<?= $userID ?>'">
                Cancel
            </button>
        </form>

    </div>

</div>

</body>
</html>
