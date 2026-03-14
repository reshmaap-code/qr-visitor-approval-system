<?php
session_start();
include '../config.php';

if (!isset($_SESSION['guard_logged_in'])) {
    header("Location: guard_login.php");
    exit();
}

// AJAX call
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $sql = "SELECT v.visitor_name, v.phone_number, v.visitor_email, v.flat_number, v.visitor_datetime, v.status, 
                   o.auto_reject_mode, o.vacation_start, o.vacation_end
            FROM visitor_requests v
            JOIN owner_users o ON v.flat_number = o.flat_number
            ORDER BY v.visitor_datetime DESC";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $statusClass = '';
            switch (strtolower($row['status'])) {
                case 'approved': $statusClass = 'status-approved'; break;
                case 'pending': $statusClass = 'status-pending'; break;
                case 'rejected': $statusClass = 'status-rejected'; break;
            }

            $ownerMode = "Normal";
            if ($row['auto_reject_mode'] == 1) {
                $ownerMode = "🚫 Auto-Reject Mode";
                if (!empty($row['vacation_start']) && !empty($row['vacation_end'])) {
                    $ownerMode .= "<br><span class='auto-reject'>Vacation: "
                               . htmlspecialchars($row['vacation_start']) . " to "
                               . htmlspecialchars($row['vacation_end']) . "</span>";
                }
            }

            echo "<tr>
                    <td>" . htmlspecialchars($row['visitor_name']) . "</td>
                    <td>" . htmlspecialchars($row['phone_number']) . "</td>
                    <td>" . htmlspecialchars($row['visitor_email']) . "</td>
                    <td>" . htmlspecialchars($row['flat_number']) . "</td>
                    <td>" . htmlspecialchars($row['visitor_datetime']) . "</td>
                    <td class='$statusClass'>" . ucfirst($row['status']) . "</td>
                    <td>$ownerMode</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No visitor requests found.</td></tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guard Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f2f8fc, #dceefd);  
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        h2 {
            text-align: center;
            color: #003366;
            font-size: 28px;
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: 700;
            border-bottom: 3px solid #003366;
            display: inline-block;
            padding-bottom: 8px;
        }

        .dashboard-container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
            background: #ffffffee;
            border-radius: 12px;
            padding: 20px 30px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            font-size: 15px;
        }

        th {
            background-color: #d5e4eeff;
            color: #003366;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5faff;
        }

        .status-pending { color: #ff8c00; font-weight: bold; }
        .status-approved { color: #008000; font-weight: bold; }
        .status-rejected { color: #d00000; font-weight: bold; }
        .auto-reject { font-size: 12px; color: #555; }

        .logout {
            text-align: center;
            margin-top: 20px;
        }

        .logout a {
            display: inline-block;
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

        .header-box {
            text-align: center;
            margin-bottom: 10px;
        }

        .icon {
            font-size: 36px;
            color: #003366;
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="header-box">
    <h2>🛡️Guard Dashboard - Visitor Request Status</h2>
</div>


<table>
    <thead>
        <tr>
            <th>Visitor Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Flat Number</th>
            <th>Visit Date/Time</th>
            <th>Status</th>
            <th>Owner Mode</th>
        </tr>
    </thead>
    <tbody id="visitor-table-body">
        <tr><td colspan="7">Loading...</td></tr>
    </tbody>
</table>

<div class="logout">
    <a href="guard_logout.php">🚪 Logout</a>
</div>

<script>
function loadVisitors() {
    fetch("guard_dashboard.php?ajax=1")
        .then(res => res.text())
        .then(html => document.getElementById("visitor-table-body").innerHTML = html)
        .catch(err => console.error(err));
}

setInterval(loadVisitors, 5000);
loadVisitors(); // initial load
</script>

</body>
</html>
