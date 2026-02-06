
<?php
session_start();
include "db.php";

if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'Donor') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (!isset($_GET['eventID'])) {
    echo "Invalid event.";
    exit();
}

$eventID = $_GET['eventID'];

$sqlEvent = "SELECT * FROM event
             WHERE eventID = '$eventID'
             AND status = 'published'
             AND eventDate >= CURDATE()";

$resultEvent = mysqli_query($conn, $sqlEvent);

if (mysqli_num_rows($resultEvent) != 1) {
    echo "Event not available.";
    exit();
}

$event = mysqli_fetch_assoc($resultEvent);

if (isset($_POST['book'])) {

    if ($event['availableSlots'] <= 0) {
        echo "<script>alert('This event is full');</script>";
    } else {

        $checkSql = "SELECT * FROM appointment
                     WHERE userID = '$userID'
                     AND eventID = '$eventID'";
        $checkResult = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "<script>
                alert('You have already booked this event.');
                window.location.href = 'viewEvents.php';
            </script>";
            exit();
        }

        $appointmentTime = $_POST['appointmentTime'];

        $sqlInsert = "INSERT INTO appointment
            (userID, eventID, appointmentDate, appointmentTime, appointmentVenue)
            VALUES
            ('$userID', '$eventID', '{$event['eventDate']}', '$appointmentTime', '{$event['eventVenue']}')";

        if (mysqli_query($conn, $sqlInsert)) {

            $sqlUpdate = "UPDATE event
                          SET availableSlots = availableSlots - 1
                          WHERE eventID = '$eventID'";
            mysqli_query($conn, $sqlUpdate);

            echo "<script>
                alert('Appointment booked successfully!');
                window.location.href = 'viewEvents.php';
            </script>";
        } else {
            echo "<script>alert('Booking failed');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>

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

        .container {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .event-box, .slot-box {
            border: 2px solid #ccc;
            padding: 20px;
            margin-top: 20px;
        }

        .slots {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .slots button {
            padding: 8px 16px;
            border: 1px solid #999;
            background: #fff;
            cursor: pointer;
        }

        .slots button.active {
            background: #dbeafe;
            border-color: #2563eb;
        }

        .confirm-btn {
            margin-top: 30px;
            padding: 8px 30px;
            font-size: 16px;
        }
    </style>

    <script>
        function selectTime(time) {
            document.getElementById("appointmentTime").value = time;
            document.querySelectorAll(".slots button").forEach(btn => {
                btn.classList.remove("active");
            });
            event.target.classList.add("active");
        }
    </script>
</head>

<body>

<div class="container">

    <h2>BOOK APPOINTMENT</h2>

    <a href="viewEvents.php">← Back to Available Event</a>

    <div class="event-box">
        <p><b>Event:</b> <?= $event['eventName'] ?></p>
        <p><b>Date:</b> <?= $event['eventDate'] ?></p>
        <p><b>Time:</b>
            <?= substr($event['eventStartTime'], 0, 5) ?>
            -
            <?= substr($event['eventEndTime'], 0, 5) ?>
        </p>
        <p><b>Venue:</b> <?= $event['eventVenue'] ?></p>
    </div>

    <?php if ($event['availableSlots'] > 0) { ?>
    <form method="POST">

        <div class="slot-box">
            <p><b>Choose an available time slot</b></p>

            <input type="time"
           name="appointmentTime"
           min="<?php echo substr($event['eventStartTime'], 0, 5); ?>"
           max="<?php echo substr($event['eventEndTime'], 0, 5); ?>"
           required>
        </div>

        <div style="text-align:center;">
            <button type="submit" name="book" class="confirm-btn">
                Confirm
            </button>
        </div>

    </form>
    <?php } else { ?>
        <p>This event is fully booked.</p>
    <?php } ?>

</div>

</body>
</html>
