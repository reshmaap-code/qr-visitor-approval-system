<?php
// Include the qrlib.php file
include 'phpqrcode/qrlib.php';

// Data to encode in QR
$data = "http://10.86.248.177/smart-visitor-app/index.html"; // Change this to your local IP

// File path to save QR
$filename = 'qr_codes/visitor_form_qr.png';

// Generate QR code and save to file
QRcode::png($data, $filename, 'L', 8, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📱 Visitor Form QR Code</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #deebf7, #b6d7f2);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        color: #003366;
    }

    h2 {
        font-size: 26px;
        font-weight: 700;
        color: #003366;
        margin-bottom: 25px;
        border-bottom: 2px solid #003366;
        padding-bottom: 10px;
    }

    .qr-box {
        background: #ffffff;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        text-align: center;
    }

    img {
        margin-top: 10px;
        width: 220px;
        height: 220px;
    }

    p {
        margin-top: 20px;
        font-size: 15px;
        color: #004080;
    }
</style>
</head>
<body>
    <h2>📱 Scan to Open Visitor Form</h2>

    <div class="qr-box">
        <img src="<?php echo $filename; ?>" alt="QR Code">
        <p>Use your phone’s camera or any QR scanner to open the visitor form.</p>
    </div>
</body>
</html>
