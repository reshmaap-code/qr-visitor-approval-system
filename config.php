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

// Gmail credentials (use App Password, not real password)
define("GMAIL_USER", "yourprojectemail1@gmail.com");
define("GMAIL_PASS", "busb titq hxlq mgoq");
?>
