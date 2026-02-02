<?php
// Edit User Account Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$message = '';
$user = null;

// Get user ID
$userID = $_GET['id'] ?? 0;
if (!$userID) {
    die("User ID is required");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM user WHERE userID = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $fullName = $_POST['fullName'];
    $userEmail = $_POST['userEmail'];
    $userPhone = $_POST['userPhone'];
    $userRole = $_POST['userRole'];
    $donorBloodType = $_POST['donorBloodType'];
    $eligibilityStatus = $_POST['eligibilityStatus'];

    try {
        $update = $pdo->prepare("
            UPDATE user SET
                userName = ?,
                userEmail = ?,
                userPhone = ?,
                userRole = ?,
                donorBloodType = ?,
                eligibilityStatus = ?
            WHERE userID = ?
        ");
        $update->execute([
            $fullName,
            $userEmail,
            $userPhone,
            $userRole,
            $donorBloodType,
            $eligibilityStatus,
            $userID
        ]);

        // Refresh data
        $stmt->execute([$userID]);
        $user = $stmt->fetch();

        $message = "User updated successfully!";
    } catch (PDOException $e) {
        $message = "Update failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User Account</title>

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

        h1 { margin-top: 0; }

        label { font-weight: bold; }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
        }

        .field {
            margin-bottom: 18px;
        }

        button {
            padding: 8px 16px;
            margin-right: 10px;
            cursor: pointer;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="page-container">

    <a href="manage_users.php" class="back-link">← Back to Manage Users</a>

    <h1>Edit User Account</h1>

    <?php if ($message): ?>
        <p><strong><?= $message ?></strong></p>
    <?php endif; ?>

    <form method="POST">

        <div class="field">
            <label>Full Name*</label>
            <input type="text" name="fullName" value="<?= htmlspecialchars($user['userName']) ?>" required>
        </div>

        <div class="field">
            <label>Username / Email*</label>
            <input type="email" name="userEmail" value="<?= htmlspecialchars($user['userEmail']) ?>" required>
        </div>

        <div class="field">
            <label>Phone</label>
            <input type="text" name="userPhone" value="<?= htmlspecialchars($user['userPhone']) ?>">
        </div>

        <div class="field">
            <label>Role</label>
            <select name="userRole">
                <option value="admin" <?= $user['userRole']=='admin'?'selected':'' ?>>Admin</option>
                <option value="donor" <?= $user['userRole']=='donor'?'selected':'' ?>>Donor</option>
                <option value="hospital" <?= $user['userRole']=='hospital'?'selected':'' ?>>Hospital</option>
                <option value="organizer" <?= $user['userRole']=='organizer'?'selected':'' ?>>Event Organizer</option>
            </select>
        </div>

        <div class="field">
            <label>Blood Type</label>
            <select name="donorBloodType">
                <?php
                $types = ['N/A','O+','O-','A+','A-','B+','B-','AB+','AB-'];
                foreach ($types as $type) {
                    $sel = $user['donorBloodType']==$type ? 'selected' : '';
                    echo "<option value='$type' $sel>$type</option>";
                }
                ?>
            </select>
        </div>

        <div class="field">
            <label>Status</label>
            <select name="eligibilityStatus">
                <option value="active" <?= $user['eligibilityStatus']=='active'?'selected':'' ?>>Active</option>
                <option value="eligible" <?= $user['eligibilityStatus']=='eligible'?'selected':'' ?>>Eligible</option>
                <option value="pending" <?= $user['eligibilityStatus']=='pending'?'selected':'' ?>>Pending</option>
                <option value="inactive" <?= $user['eligibilityStatus']=='inactive'?'selected':'' ?>>Inactive</option>
            </select>
        </div>

        <button type="submit" name="update_user">Update</button>
        <button type="button" onclick="window.location.href='manage_users.php'">Cancel</button>

    </form>

</div>

</body>
</html>
