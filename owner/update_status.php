<?php
session_start();
include '../config.php';

if (!isset($_SESSION['owner_logged_in'])) {
    header("Location: owner_login.html");
    exit();
}

$owner_username = $_SESSION['owner_username'];

// ✅ Case 1: Update Auto-Reject / Vacation Mode Settings
if (isset($_POST['auto_reject_mode']) || isset($_POST['vacation_start'])) {
    $auto_reject = isset($_POST['auto_reject_mode']) ? 1 : 0;
    $vac_start = !empty($_POST['vacation_start']) ? $_POST['vacation_start'] : NULL;
    $vac_end = !empty($_POST['vacation_end']) ? $_POST['vacation_end'] : NULL;

    $stmt = $conn->prepare("UPDATE owner_users 
        SET auto_reject_mode = ?, vacation_start = ?, vacation_end = ? 
        WHERE username = ?");
    $stmt->bind_param("isss", $auto_reject, $vac_start, $vac_end, $owner_username);
    $stmt->execute();

    header("Location: owner_dashboard.php");
    exit();
}

// ✅ Case 2: Approve / Reject Visitor Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    // Update status in DB
    $owner_msg = "Request " . strtolower($action) . " by owner.";
    $stmt = $conn->prepare("UPDATE visitor_requests SET status = ?, owner_msg = ? WHERE id = ?");
    $stmt->bind_param("ssi", $action, $owner_msg, $id);
    $stmt->execute();

    // Fetch visitor details for email
    $stmt2 = $conn->prepare("SELECT visitor_name, phone_number FROM visitor_requests WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $visitor = $result->fetch_assoc();

    // Fetch owner email
    $stmt3 = $conn->prepare("SELECT email FROM owner_users WHERE username = ?");
    $stmt3->bind_param("s", $owner_username);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $owner = $result3->fetch_assoc();

    // ✅ Send Email Notification
    if ($owner && !empty($owner['email'])) {
        $to = $owner['email'];  
        $subject = "Visitor Request " . $action;
        $message = "Hello " . $owner_username . ",\n\n" .
                   "Visitor: " . $visitor['visitor_name'] . "\n" .
                   "Phone: " . $visitor['phone_number'] . "\n" .
                   "Status: " . $action . "\n\n" .
                   "Message: " . $owner_msg . "\n\n" .
                   "Regards,\nVisitor Approval System";

        $headers = "From: no-reply@yourdomain.com";

        mail($to, $subject, $message, $headers);
    }

    header("Location: owner_dashboard.php");
    exit();
}

// Default redirect
header("Location: owner_dashboard.php");
exit();
?>
