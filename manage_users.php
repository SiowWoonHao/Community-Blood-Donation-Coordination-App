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
</head>
<body>
    <h1>Manage Users</h1>
    
    <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px;">
        <form method="GET">
            <label>Search:</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Name or Email">
            
            <label>Role:</label>
            <select name="role">
                <option value="all" <?php echo $selectedRole === 'all' ? 'selected' : ''; ?>>All Roles</option>
                <option value="donor" <?php echo $selectedRole === 'donor' ? 'selected' : ''; ?>>Donor</option>
                <option value="hospital" <?php echo $selectedRole === 'hospital' ? 'selected' : ''; ?>>Hospital</option>
                <option value="organizer" <?php echo $selectedRole === 'organizer' ? 'selected' : ''; ?>>Event Organizer</option>
                <option value="admin" <?php echo $selectedRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            
            <button type="submit">Search</button>
            <button type="button" onclick="window.location.href='manage_users.php'">Reset</button>
        </form>
    </div>
    
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Blood Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No users found. Insert sample data first.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['userID']; ?></td>
                        <td><?php echo htmlspecialchars($user['userName']); ?></td>
                        <td><?php echo htmlspecialchars($user['userEmail']); ?></td>
                        <td><?php echo $user['userPhone']; ?></td>
                        <td>
                            <?php 
                            $roleColors = [
                                'admin' => 'red',
                                'hospital' => 'blue',
                                'organizer' => 'orange',
                                'donor' => 'green'
                            ];
                            $color = $roleColors[$user['userRole']] ?? 'gray';
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo ucfirst($user['userRole']); ?>
                            </span>
                        </td>
                        <td><?php echo $user['donorBloodType']; ?></td>
                        <td style="color: <?php 
                            $status = strtolower($user['eligibilityStatus']);
                            if ($status === 'eligible' || $status === 'active') {
                                echo 'green';
                            } elseif ($status === 'pending') {
                                echo 'orange';
                            } else {
                                echo 'red';
                            }
                        ?>; font-weight: bold;">
                            <?php echo ucfirst($user['eligibilityStatus']); ?>
                        </td>
                        <td>
                            <!-- Edit Button -->
                            <button onclick="window.location.href='edit_user.php?id=<?php echo $user['userID']; ?>'">Edit</button>
                            <!-- Deactivate/Activate Button -->
                            <?php if (strtolower($user['eligibilityStatus']) !== 'inactive'): ?>
                                <button onclick="window.location.href='deactivate_user.php?id=<?php echo $user['userID']; ?>'" style="color: red;">Deactivate</button>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="userID" value="<?php echo $user['userID']; ?>">
                                    <button type="submit" name="activate_user" onclick="return confirm('Activate user <?php echo htmlspecialchars($user['userName']); ?>?')" style="color: green;">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <br>
    <p>Total Users: <?php echo count($users); ?></p>
    
    <script>
        function editUser(userID) {
            // For now, just show an alert
            // In real implementation, redirect to edit_user.php
            alert('Edit user ID: ' + userID);
            // window.location.href = 'edit_user.php?id=' + userID;
        }
    </script>
</body>
</html>