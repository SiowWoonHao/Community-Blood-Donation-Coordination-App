<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Dashboard</title>

<style>
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

/* 🌈 SAME ANIMATED GRADIENT */
body {
    margin: 0;
    min-height: 100vh;
    padding: 40px;
    background: linear-gradient(
        120deg,
        #f5f7fa,
        #b8f7d4,
        #9be7ff,
        #c7d2fe,
        #fef9c3
    );
    background-size: 300% 300%;
    animation: gradientMove 18s ease infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* MAIN CARD */
.dashboard {
    max-width: 700px;
    margin: auto;
    background: #fff;
    padding: 40px 30px;
    border-radius: 18px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    text-align: center;
}

/* TITLE */
.dashboard h1 {
    margin-top: 0;
    margin-bottom: 40px;
}

/* BUTTONS */
.dashboard button {
    width: 320px;
    padding: 15px 30px;
    font-size: 18px;
    margin-bottom: 20px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}

.dashboard button:hover {
    background: #f0f0f0;
}
</style>
</head>

<body>

<div class="dashboard">

    <h1>Hospital Dashboard</h1>

    <div>
        <button onclick="window.location.href='view_inventory.php'">
            View Blood Inventory
        </button>
    </div>

    <div>
        <button onclick="window.location.href='update_inventory.php'">
            Update Blood Inventory
        </button>
    </div>

    <div>
        <button onclick="window.location.href='urgent_request.php'">
            Urgent Blood Request
        </button>
    </div>

    <div>
        <button onclick="window.location.href='inventory_report.php'">
            Blood Inventory Report
        </button>
    </div>

</div>

</body>
</html>

