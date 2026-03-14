<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_logged_in'])) {
    header("Location: owner_login.html");
    exit();
}

// Get the logged-in owner's username
$owner_username = $_SESSION['owner_username'];

// Step 1: Get flat_number and auto-reject settings for this owner
$query = "SELECT flat_number, auto_reject_mode, vacation_start, vacation_end FROM owner_users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $owner_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $owner_flat = $row['flat_number'];
    $auto_reject = $row['auto_reject_mode'];
    $vac_start = $row['vacation_start'];
    $vac_end = $row['vacation_end'];
} else {
    echo "Error: Owner not found.";
    exit();
}

// Step 2: Fetch visitor requests for this flat
$sql = "SELECT * FROM visitor_requests WHERE flat_number = ? ORDER BY visitor_datetime DESC";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("s", $owner_flat);
$stmt2->execute();
$requests = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard</title>
    
     <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #f2f8fc, #dceefd);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            background: #ffffffee;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }

        .header-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .icon {
            font-size: 36px;
            color: #003366;
            display: block;
            margin-bottom: 5px;
        }

        .header-box h2 {
            font-size: 28px;
            color: #003366;
            font-weight: 700;
            border-bottom: 3px solid #003366;
            display: inline-block;
            padding-bottom: 8px;
            margin: 0;
        }

        .header-box p {
            font-size: 16px;
            color: #333;
            margin-top: 10px;
        }

        .header-box a {
            text-decoration: none;
            color: #003366;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }

        .header-box a:hover {
            text-decoration: underline;
            color: #0055aa;
        }

        /* Auto Reject Box */
        .settings-box {
            background-color: #e9f4ff;
            border: 1px solid #007BFF;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.05);
        }

        .settings-box h3 {
            margin: 0 0 15px;
            color: #007BFF;
        }

        .settings-box label {
            display: block;
            font-weight: 600;
            margin: 8px 0;
        }

        .settings-box input[type="date"],
        .settings-box input[type="checkbox"] {
            margin: 5px;
            padding: 5px;
        }

        .settings-box button {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .settings-box button:hover {
            background-color: #0056b3;
        }

        /* Requests Section */
        .requests-container {
            background-color: #fff;
            border: 2px solid #007BFF;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .requests-container h3 {
            color: #007BFF;
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
            border-bottom: 2px solid #007BFF;
            display: inline-block;
            padding-bottom: 5px;
        }

        /* Visitor Grid */
        .visitor-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .visitor-box {
            background-color: #f7fbff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            width: 260px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .visitor-box:hover {
            transform: translateY(-3px);
        }

        .visitor-box p {
            margin: 6px 0;
            line-height: 1.4;
        }

        .visitor-box strong {
            color: #003366;
        }

        .visitor-box button {
            margin-right: 5px;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .visitor-box button[name="action"][value="Approved"] {
            background-color: #28a745;
            color: white;
        }

        .visitor-box button[name="action"][value="Rejected"] {
            background-color: #dc3545;
            color: white;
        }

        .visitor-box button:hover {
            opacity: 0.85;
        }

        /* Logout at bottom */
        .logout {
            text-align: center;
            margin-top: 25px;
        }

        .logout a {
            background: linear-gradient(to right, #003366, #007bff);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        .logout a:hover {
            background: linear-gradient(to right, #007bff, #003366);
        }
    </style>
</head>
<body>
    <div class="container">
    
    <div class="header-box">
    <h2>Welcome, <?php echo htmlspecialchars($owner_username); ?>!</h2>
    </div>
    <div class="header-box">
    <p style="margin-bottom:10px;">
        Your Flat Number: <strong><?php echo htmlspecialchars($owner_flat); ?></strong></p><br>
    

    </div>

    <!-- Auto Reject Settings -->
    <div class="settings-box">
        <h3>Auto-Reject / Vacation Mode</h3>
        <form method="POST" action="update_status.php">
            <label>
                <input type="checkbox" name="auto_reject_mode" value="1" 
                    <?php if($auto_reject) echo "checked"; ?>>
                Enable Auto-Reject Mode
            </label>
            <br><br>
            <label>Vacation Start:</label>
            <input type="date" name="vacation_start" value="<?php echo $vac_start; ?>">
            <label>Vacation End:</label>
            <input type="date" name="vacation_end" value="<?php echo $vac_end; ?>">
            <br><br>
            <button type="submit">Save Settings</button>
        </form>
    </div>
    <div class="requests-container">
    <h3>Visitor Approval Requests</h3>
    <div class="visitor-grid">
        <?php if ($requests->num_rows > 0): ?>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <div class="visitor-box">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['visitor_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone_number']); ?></p>
                    <p><strong>Purpose:</strong> <?php echo htmlspecialchars($row['purpose']); ?></p>
                    <p><strong>Visit Time:</strong> <?php echo htmlspecialchars($row['visitor_datetime']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($row['status']); ?></p>
                    <p><strong>Owner Message:</strong> <?php echo htmlspecialchars($row['owner_msg']); ?></p>

                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" action="update_status.php">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="action" value="Approved">Approve</button>
                            <button type="submit" name="action" value="Rejected">Reject</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No visitor requests for your flat.</p>
        <?php endif; ?>
    </div> <!-- close visitor-grid here -->
</div> <!-- close request-container here -->
<div class="logout">
        <a href="owner_logout.php">🚪 Logout</a>
</div>
</body>
</html>
