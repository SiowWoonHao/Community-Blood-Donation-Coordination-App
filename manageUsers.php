<?php
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$users = [];
$searchTerm = '';
$selectedRole = '';

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
        echo "<p style='color:red;'>Error: {$e->getMessage()}</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_user'])) {
    $userID = $_POST['userID'];

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $stmt = $pdo->prepare("UPDATE user SET userStatus = 'Inactivate' WHERE userID = ?");
    $stmt->execute([$userID]);

    header("Location: manageUsers.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_user'])) {
    $userID = $_POST['userID'];

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $stmt = $pdo->prepare("UPDATE user SET userStatus = 'Activate' WHERE userID = ?");
    $stmt->execute([$userID]);

    header("Location: manageUsers.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
</head>
<body>

<h1>Manage Users</h1>

<form method="GET">
    Search:
    <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>">

    Role:
    <select name="role">
        <option value="all">All</option>
        <option value="Donor">Donor</option>
        <option value="Hospital">Hospital</option>
        <option value="Organizer">Organizer</option>
        <option value="Admin">Admin</option>
    </select>

    <button type="submit">Search</button>
</form>
<p><a href="createUser.php">Add User</a></p>

<br>

<table border="1" cellpadding="8">
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

<?php if (empty($users)): ?>
    <tr>
        <td colspan="8" align="center">No users found</td>
    </tr>
<?php else: ?>
<?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['userID'] ?></td>
        <td><?= htmlspecialchars($user['userName']) ?></td>
        <td><?= htmlspecialchars($user['userEmail']) ?></td>
        <td><?= $user['userPhone'] ?></td>
        <td><?= $user['userRole'] ?></td>
        <td><?= $user['donorBloodType'] ?? '-' ?></td>

        <td style="font-weight:bold; color:<?= $user['userStatus'] === 'Activate' ? 'green' : 'red' ?>">
            <?= $user['userStatus'] ?>
        </td>

        <td>
            <!-- Edit Button -->
            <button onclick="window.location.href='editUser.php?id=<?= $user['userID'] ?>'">
                Edit
            </button>

            <!-- Activate / Deactivate -->
            <?php if ($user['userStatus'] === 'Activate'): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
                    <button type="submit"
                            name="deactivate_user"
                            style="color:red;"
                            onclick="return confirm('Deactivate this user?')">
                        Deactivate
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
                    <button type="submit"
                            name="activate_user"
                            style="color:green;"
                            onclick="return confirm('Activate this user?')">
                        Activate
                    </button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>

</table>

<p>Total Users: <?= count($users) ?></p>
<p><a href="adminDashboard.php">Back to Dashboard</a></p>
</body>
</html>
