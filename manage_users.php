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
            max-width: 1200px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 30px 40px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        h1 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background: #f4f4f4;
        }

        .filter-box {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        button {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            cursor: pointer;
            background: #fff;
        }

        button:hover {
            background: #eee;
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

    <a href="admin_dashboard.php" class="back-link">← Back to Admin Dashboard</a>

    <h1>Manage Users</h1>
    
    <div class="filter-box">
    <form method="GET" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

        <label>Search:</label>
        <input type="text" name="search"
               value="<?php echo htmlspecialchars($searchTerm); ?>"
               placeholder="Name or Email">

        <label>Role:</label>
        <select name="role">
            <option value="all">All Roles</option>
            <option value="donor">Donor</option>
            <option value="hospital">Hospital</option>
            <option value="organizer">Event Organizer</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='manage_users.php'">Reset</button>

        <button type="button"
                onclick="window.location.href='create_user.php'"
                style="margin-left:auto;">
            + New user
        </button>

    </form>
</div>


    <table>
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
                <td colspan="8" style="text-align:center;">No users found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['userID'] ?></td>
                <td><?= htmlspecialchars($user['userName']) ?></td>
                <td><?= htmlspecialchars($user['userEmail']) ?></td>
                <td><?= $user['userPhone'] ?></td>
                <td><?= ucfirst($user['userRole']) ?></td>
                <td><?= $user['donorBloodType'] ?></td>
                <td><?= ucfirst($user['eligibilityStatus']) ?></td>
                <td>
                    <button onclick="window.location.href='edit_user.php?id=<?= $user['userID'] ?>'">Edit</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <p>Total Users: <?= count($users) ?></p>

</div>

</body>
</html>


