<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    if (empty($username) || empty($input_password)) {
        echo "❌ Please enter both username and password.";
        exit();
    }

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM owner_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($input_password, $row['password'])) {
            $_SESSION['owner_logged_in'] = true;
            $_SESSION['owner_username'] = $row['username'];
            $_SESSION['owner_flat_number'] = $row['flat_number']; // ⬅️ store flat_number for future use

            header("Location: owner_dashboard.php");
            exit();
        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ Username not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
