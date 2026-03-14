<?php
session_start();
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Gmail SMTP function
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USER;       // from config.php
        $mail->Password   = GMAIL_PASS;       // app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom(GMAIL_USER, 'Smart Visitor App');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("❌ Email to $to failed: {$mail->ErrorInfo}");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $visitor_name     = $_POST['visitor_name'] ?? '';
    $phone_number     = $_POST['phone_number'] ?? '';
    $visitor_email    = $_POST['visitor_email'] ?? '';
    $flat_number      = $_POST['flat_number'] ?? '';
    $purpose          = $_POST['purpose'] ?? '';
    $visitor_datetime = $_POST['visitor_datetime'] ?? date("Y-m-d H:i:s");

    $status = 'pending';
    $owner_msg = "New visitor $visitor_name at $visitor_datetime";

    // --- Step 2: Check Owner Auto-Reject / Vacation Mode ---
    $stmt_owner = $conn->prepare("SELECT auto_reject_mode, vacation_start, vacation_end FROM owner_users WHERE flat_number = ?");
    $stmt_owner->bind_param("s", $flat_number);
    $stmt_owner->execute();
    $result_owner = $stmt_owner->get_result();

    if ($result_owner->num_rows > 0) {
        $row_owner = $result_owner->fetch_assoc();
        $auto_reject = $row_owner['auto_reject_mode'];
        $vac_start   = $row_owner['vacation_start'];
        $vac_end     = $row_owner['vacation_end'];
        $visit_date  = date("Y-m-d", strtotime($visitor_datetime));

        if ($auto_reject == 1) {
            if (!empty($vac_start) && !empty($vac_end)) {
                // Reject only if visitor date falls within vacation
                if ($visit_date >= $vac_start && $visit_date <= $vac_end) {
                    $status = 'Rejected';
                    $owner_msg = "🚫 Auto-Reject / Vacation Active: $vac_start to $vac_end";
                }
            } else {
                // No vacation dates but auto-reject ON → reject all
                $status = 'Rejected';
                $owner_msg = "🚫 Auto-Reject Active: Owner not available";
            }
        }
    }

    // Insert visitor request
    $stmt = $conn->prepare("INSERT INTO visitor_requests 
        (visitor_name, phone_number, visitor_email, flat_number, purpose, visitor_datetime, status, owner_msg)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssss",
        $visitor_name,
        $phone_number,
        $visitor_email,
        $flat_number,
        $purpose,
        $visitor_datetime,
        $status,
        $owner_msg
    );

    if ($stmt->execute()) {
        // --- Get Owner Email from DB ---
        $owner_email = null;
        $stmt_owner = $conn->prepare("SELECT email FROM owner_users WHERE flat_number = ?");
        $stmt_owner->bind_param("s", $flat_number);
        $stmt_owner->execute();
        $result_owner = $stmt_owner->get_result();
        if ($result_owner->num_rows > 0) {
            $row_owner = $result_owner->fetch_assoc();
            $owner_email = $row_owner['email'];
        }

        // --- Get Guard Email (if you have guard_users table) ---
        $guard_email = null;
        $sql_guard = "SELECT email FROM guard_users LIMIT 1"; 
        $result_guard = $conn->query($sql_guard);
        if ($result_guard && $result_guard->num_rows > 0) {
            $row_guard = $result_guard->fetch_assoc();
            $guard_email = $row_guard['email'];
        }

        // --- Notify Owner ---
        if ($owner_email) {
            $subject = "New Visitor Request for Your Flat $flat_number";
            $body = "
                Hello Owner,<br><br>
                You have a new visitor request:<br>
                <b>Name:</b> $visitor_name<br>
                <b>Phone:</b> $phone_number<br>
                <b>Email:</b> $visitor_email<br>
                <b>Purpose:</b> $purpose<br>
                <b>Visit Time:</b> $visitor_datetime<br>
                <b>Status:</b> $status<br>
                <b>Message:</b> $owner_msg<br><br>
                Please log in to your dashboard to approve or reject.
            ";
            sendMail($owner_email, $subject, $body);
        }

        // --- Notify Guard ---
        if ($guard_email) {
            $subject_g = "New Visitor Request - Flat $flat_number";
            $body_g = "
                Hello Guard,<br><br>
                Visitor request submitted:<br>
                <b>Name:</b> $visitor_name<br>
                <b>Phone:</b> $phone_number<br>
                <b>Email:</b> $visitor_email<br>
                <b>Flat:</b> $flat_number<br>
                <b>Purpose:</b> $purpose<br>
                <b>Visit Time:</b> $visitor_datetime<br>
                <b>Status:</b> $status<br>
                <b>Owner Message:</b> $owner_msg<br>
            ";
            sendMail($guard_email, $subject_g, $body_g);
        }

        header("Location: thank_you.html");
        exit();
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "Form not submitted properly.";
}

$conn->close();
?>
