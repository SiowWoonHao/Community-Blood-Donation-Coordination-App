<?php
// Manage Users Page
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$users = [];
$searchTerm = '';
$selectedRole = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['search'] ?? '';
    $selectedRole = $_GET['role'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        $sql = "SELECT * FROM user WHERE 1=1";
        $params = [];
        
        if (!empty($searchTerm)) {
            $sql .= " AND (userName LIKE ? OR userEmail LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }
        
        if (!empty($selectedRole) && $selectedRole !== 'all') {
            $sql .= " AND userRole = ?";
            $params[] = $selectedRole;
        }
        
        $sql .= " ORDER BY userID";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Handle deactivate user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_user'])) {
    $userID = $_POST['userID'] ?? '';
    
    if (!empty($userID)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            // Update status to 'inactive' instead of deleting
            $sql = "UPDATE user SET eligibilityStatus = 'inactive' WHERE userID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userID]);
            
            header("Location: manage_users.php?success=User deactivated");
            exit();
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// Handle activate user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_user'])) {
    $userID = $_POST['userID'] ?? '';
    
    if (!empty($userID)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
            // Update status to 'active'
            $sql = "UPDATE user SET eligibilityStatus = 'active' WHERE userID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userID]);
            
            header("Location: manage_users.php?success=User activated");
            exit();
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .page-container {
            max-width: 1200px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        h1 {
            margin: 0 0 15px 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .toolbar select,
        .toolbar input {
            padding: 10px;
            font-size: 14px;
        }

        .toolbar input {
            flex: 1;
        }

        .toolbar button {
            padding: 10px 16px;
            cursor: pointer;
        }

        .new-user-btn {
            margin-left: auto;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        /* Actions 按钮 */
        td button {
            padding: 6px 10px;
            margin-right: 5px;
            cursor: pointer;
        }

        .footer-info {
            margin-top: 15px;
        }
    </style>
</head>

<body>

<div class="page-container">

    <h1>Manage Users</h1>

    <a href="admin_dashboard.php" class="back-link">
        ← Back to Admin Dashboard
    </a>

    <div class="toolbar">
        <form method="GET" style="display:flex; gap:15px; width:100%;">
            <select name="role">
                <option value="all">All Roles</option>
                <option value="donor">Donor</option>
                <option value="hospital">Hospital</option>
                <option value="organizer">Event Organizer</option>
                <option value="admin">Admin</option>
            </select>

            <input type="text" name="search"
                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                   placeholder="Search users......">

            <button type="submit">Search</button>

            <button type="button"
                    class="new-user-btn"
                    onclick="window.location.href='add_user.php'">
                + New user
            </button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username/Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="6" style="text-align:center;">No users found</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['userID']; ?></td>
                    <td><?php echo htmlspecialchars($user['userName']); ?></td>
                    <td><?php echo htmlspecialchars($user['userEmail']); ?></td>
                    <td><?php echo $user['userRole']; ?></td>
                    <td><?php echo $user['eligibilityStatus']; ?></td>
                    <td>
                        <button onclick="window.location.href='edit_user.php?id=<?php echo $user['userID']; ?>'">
                            Edit
                        </button>
                        <button onclick="window.location.href='deactivate_user.php?id=<?php echo $user['userID']; ?>'">
                            Deactivate
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-info">
        Total Users: <?php echo count($users); ?>
    </div>

</div>

</body>
</html>
