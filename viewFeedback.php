<?php
// viewFeedback.php - View Feedback
$host = 'localhost';
$dbname = 'cbdcdatabase';
$username = 'root';
$password = '';

$feedbackID = $_GET['id'] ?? 0;
if (empty($feedbackID)) {
    die("Feedback ID is required");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $sql = "SELECT * FROM feedback WHERE feedbackID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$feedbackID]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        die("Feedback not found");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Feedback</title>

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
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        .feedback-box {
            border: 2px solid #ccc;
            padding: 25px;
            margin-top: 20px;
        }

        .actions {
            margin-top: 25px;
        }

        button {
            padding: 6px 18px;
            margin-right: 15px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="page-container">

    <h2>View feedback</h2>

    <!-- Back -->
    <a class="back-link" href="feedback.php">
        ← Back to feedback
    </a>

    <!-- Feedback content -->
    <div class="feedback-box">
        <p>
            <strong>
                <?= htmlspecialchars($feedback['userName']) ?>
                (<?= htmlspecialchars($feedback['userEmail']) ?>)
            </strong>
        </p>

        <p>
            <?= htmlspecialchars($feedback['message']) ?>
        </p>

        <div class="actions">
            <button onclick="window.location.href='response.php?id=<?= $feedback['feedbackID'] ?>'">
                response
            </button>
        </div>
    </div>

</div>

</body>
</html>
