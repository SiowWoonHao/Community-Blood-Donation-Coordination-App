<?php
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$feedbacks = [];
$searchTerm = '';
$filter = 'all';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? 'all';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        $sql = "SELECT * FROM feedback WHERE 1=1";
        $params = [];

        if (!empty($searchTerm)) {
            $sql .= " AND (userName LIKE ? OR message LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }

        if ($filter !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $filter;
        }

        $sql .= " ORDER BY createdDate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $feedbacks = $stmt->fetchAll();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Feedback</title>

<style>
body{
    margin:0;
    min-height:100vh;
    font-family:Arial, sans-serif;
    background: linear-gradient(
        -45deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size:500% 500%;
    animation: gradientMove 14s ease infinite;
}

@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:1200px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,0.18);
}

.top-bar{
    display:flex;
    align-items:center;
    gap:15px;
}

.back-btn{
    border:1px solid #000;
    padding:8px 16px;
    background:#fff;
    cursor:pointer;
}

.filter-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:20px 0;
}

.tabs button{
    border:1px solid #000;
    background:#fff;
    padding:6px 14px;
    margin-right:8px;
    cursor:pointer;
}

.search-box input{
    padding:8px;
    width:260px;
    border:1px solid #000;
}

.table-box{
    border:1px solid #000;
    padding:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid #000;
    padding:10px;
    text-align:center;
}

.pagination{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:15px;
}

.page-btns button{
    border:1px solid #000;
    background:#fff;
    padding:4px 10px;
    margin:0 2px;
}
</style>
</head>

<body>

<div class="container">

<h2>Feedback</h2>

<div class="top-bar">
    <button class="back-btn" onclick="window.location.href='admin_dashboard.php'">
        ← Back to Admin Dashboard
    </button>
</div>

<div class="filter-bar">

    <div class="tabs">
        <button>Pending (2)</button>
        <button>Resolved (2)</button>
        <button>Replied (1)</button>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="search"
               value="<?php echo htmlspecialchars($searchTerm); ?>"
               placeholder="Search feedback......">
    </form>

</div>

<div class="table-box">

<p>Showing pending feedback</p>

<table>
    <thead>
        <tr>
            <th>Donor</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php if (empty($feedbacks)): ?>
        <tr>
            <td colspan="4">No feedback found.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($feedbacks as $fb): ?>
        <tr>
            <td><?php echo htmlspecialchars($fb['userName']); ?></td>
            <td><?php echo date('M d, Y', strtotime($fb['createdDate'])); ?></td>
            <td><?php echo ucfirst($fb['status']); ?></td>
            <td>
                <button onclick="window.location.href='viewFeedback.php?id=<?php echo $fb['feedbackID']; ?>'">
                    View
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
</table>

<div class="pagination">
    <div class="page-btns">
        <button>&lt;</button>
        <button>1</button>
        <button>2</button>
        <button>3</button>
        <button>4</button>
        <button>5</button>
        <button>&gt;</button>
    </div>

    <div>
        5 / page
    </div>
</div>

<p style="text-align:center;margin-top:10px;">
    showing 1 to <?php echo count($feedbacks); ?> feedback
</p>

</div>

</div>

</body>
</html>
