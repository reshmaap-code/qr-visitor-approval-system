<?php
$host = "localhost";
$username = "root";
$password = ""; 
$database = "smart_visitor_db";

// Create DB connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Gmail credentials
define("GMAIL_USER", "your_email@gmail.com");
define("GMAIL_PASS", "your_app_password");
?>
