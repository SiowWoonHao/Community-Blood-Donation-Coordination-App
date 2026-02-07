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
    
    if (empty($fullName) || empty($userEmail) || empty($userRole)) {
        $message = "Please fill in required fields";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
    } else {
        try {
            $checkSql = "SELECT COUNT(*) FROM user WHERE userEmail = ? AND userID != ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$userEmail, $userID]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Email already exists for another user";
            } else {
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
    background-size:400% 400%;
    animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.container{
    max-width:900px;
    margin:60px auto;
    background:#fff;
    padding:35px 45px;
    border-radius:16px;
    box-shadow:0 15px 30px rgba(0,0,0,0.2);
}

.top-bar{
    border:2px solid #ccc;
    padding:12px;
    margin-bottom:25px;
}

.top-bar a{
    text-decoration:none;
    color:#000;
    font-weight:500;
}

.form-group{
    margin-bottom:18px;
}

label{
    font-weight:600;
    display:block;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:14px;
}

input:focus, select:focus{
    outline:none;
    border-color:#6a5cff;
}

.btn{
    padding:10px 22px;
    border-radius:10px;
    border:none;
    background:#6a5cff;
    color:white;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
}

.btn.secondary{
    background:#ddd;
    color:#333;
}

.btn:hover{ opacity:0.85; }

.message{
    font-weight:600;
    margin-bottom:20px;
}

.info-box{
    margin-top:30px;
    background:#f9f9ff;
    padding:20px;
    border-radius:12px;
}

table{
    border-collapse:collapse;
    width:100%;
}

table th, table td{
    padding:10px;
    border:1px solid #ccc;
    text-align:left;
}
</style>
</head>

<body>

<div class="container">

<h2>Edit User Account</h2>

<div class="top-bar">
    ← <a href="manageUsers.php">Back to Manage Users</a>
</div>

<?php if ($message): ?>
<p class="message" style="color: <?= $success ? 'green' : 'red'; ?>">
    <?= $message; ?>
</p>
<?php endif; ?>

<?php if ($user): ?>

<form method="POST">

<div class="form-group">
    <label>Full Name</label>
    <input type="text" name="fullName" value="<?= htmlspecialchars($user['userName']); ?>" required>
</div>

<div class="form-group">
    <label>Username / Email</label>
    <input type="email" name="userEmail" value="<?= htmlspecialchars($user['userEmail']); ?>" required>
</div>

<div class="form-group">
    <label>Phone Number</label>
    <input type="text" name="userPhone" value="<?= htmlspecialchars($user['userPhone']); ?>" required>
</div>

<div class="form-group">
    <label>Role</label>
    <select name="userRole" required>
        <option value="Donor" <?= $user['userRole']==='Donor'?'selected':'' ?>>Donor</option>
        <option value="Admin" <?= $user['userRole']==='Admin'?'selected':'' ?>>Admin</option>
        <option value="Hospital" <?= $user['userRole']==='Hospital'?'selected':'' ?>>Hospital</option>
        <option value="Organizer" <?= $user['userRole']==='Organizer'?'selected':'' ?>>Event Organizer</option>
    </select>
</div>

<div class="form-group">
    <label>Blood Type</label>
    <select name="donorBloodType">
        <?php
        $types = ['N/A','O+','A+','B+','AB+','O-','A-','B-','AB-'];
        foreach($types as $t){
            echo "<option value='$t' ".($user['donorBloodType']===$t?'selected':'').">$t</option>";
        }
        ?>
    </select>
</div>

<div class="form-group">
    <label>Status</label>
    <?php if ($user['userRole']==='Donor'): ?>
        <select name="eligibilityStatus" required>
            <option value="Valid" <?= $user['eligibilityStatus']==='Valid'?'selected':'' ?>>Valid</option>
            <option value="Invalid" <?= $user['eligibilityStatus']==='Invalid'?'selected':'' ?>>Invalid</option>
        </select>
    <?php else: ?>
        <p>This field applies to Donor only.</p>
        <input type="hidden" name="eligibilityStatus" value="<?= htmlspecialchars($user['eligibilityStatus']); ?>">
    <?php endif; ?>
</div>

<br>

<button type="submit" name="update_user" class="btn">Update Account</button>
<button type="button" onclick="window.location.href='manageUsers.php'" class="btn secondary">Cancel</button>

</form>

<div class="info-box">
<h3>User Information</h3>
<table>
<tr><th>User ID</th><td><?= $user['userID']; ?></td></tr>
<tr><th>Age</th><td><?= $user['donorAge']; ?></td></tr>
<tr><th>Gender</th><td><?= $user['donorGender']; ?></td></tr>
<tr><th>Height</th><td><?= $user['donorHeight']; ?> cm</td></tr>
<tr><th>Weight</th><td><?= $user['donorWeight']; ?> kg</td></tr>
<tr><th>Organization</th><td><?= htmlspecialchars($user['organizationName']); ?></td></tr>
<tr><th>Hospital Address</th><td><?= htmlspecialchars($user['hospitalAddress']); ?></td></tr>
</table>
</div>

<?php endif; ?>

</div>
</body>
</html>
