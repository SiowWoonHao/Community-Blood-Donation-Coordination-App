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

<style>
body{
    margin:0;
    min-height:100vh;
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
@keyframes gradientBG{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:1200px;
    margin:50px auto;
    background:#fff;
    padding:30px 40px;
    border-radius:16px;
    box-shadow:0 12px 30px rgba(0,0,0,0.15);
}

.top-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:20px;
}

.filter-row{
    display:flex;
    gap:12px;
    align-items:center;
    margin-bottom:20px;
}

.filter-row select,
.filter-row input{
    padding:8px;
    font-size:14px;
}

.filter-row input{
    flex:1;
}

.filter-row .add-btn{
    padding:8px 16px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

th{
    background:#f2f2f2;
}

.actions button{
    margin-right:5px;
}
.pagination{
    margin-top:20px;
    text-align:center;
}
</style>
</head>

<body>

<div class="container">

<h2>Manage Users</h2>

<div class="top-bar">
    <a href="adminDashboard.php">← Back to Admin Dashboard</a>
</div>

<form method="GET">
<div class="filter-row">

    <select name="role">
        <option value="all">All Roles</option>
        <option value="Donor">Donor</option>
        <option value="Hospital">Hospital</option>
        <option value="Organizer">Organizer</option>
        <option value="Admin">Admin</option>
    </select>

    <input type="text"
           name="search"
           placeholder="Search users..."
           value="<?= htmlspecialchars($searchTerm) ?>">

    <button type="button"
            class="add-btn"
            onclick="window.location.href='createUser.php'">
        + New user
    </button>
</div>
</form>

<p>Showing all users</p>

<table>
<tr>
    <th>Name</th>
    <th>Username / Email</th>
    <th>Role</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php if(empty($users)): ?>
<tr>
    <td colspan="5" align="center">No users found</td>
</tr>
<?php else: foreach($users as $user): ?>
<tr>
    <td><?= htmlspecialchars($user['userName']) ?></td>
    <td><?= htmlspecialchars($user['userEmail']) ?></td>
    <td><?= $user['userRole'] ?></td>
    <td style="color:<?= $user['userStatus']=='Activate'?'green':'red' ?>">
        <?= $user['userStatus'] ?>
    </td>
    <td class="actions">
        <button onclick="location.href='editUser.php?id=<?= $user['userID'] ?>'">
            Edit
        </button>

        <?php if($user['userStatus']=='Activate'): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
            <button name="deactivate_user"
                    onclick="return confirm('Deactivate this user?')">
                Deactivate
            </button>
        </form>
        <?php else: ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
            <button name="activate_user"
                    onclick="return confirm('Activate this user?')">
                Activate
            </button>
        </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; endif; ?>
</table>

<div class="pagination">
    ‹ 1 2 3 … 10 › &nbsp;&nbsp; showing 1 to <?= count($users) ?> of <?= count($users) ?> users
</div>

</div>
</body>
</html>

