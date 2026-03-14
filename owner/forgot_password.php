<?php
include '../config.php';

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT * FROM owner_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE owner_users SET password = ? WHERE username = ?");
        $update->bind_param("ss", $hashed_password, $username);
        $update->execute();
        $success = "✅ Password changed successfully!";
    } else {
        $error = "❌ Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Reset Password</h2>
    <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <p><a href="owner_login.html">🔑 Go back to Login</a></p>

    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>New Password:</label>
        <input type="password" name="new_password" required>

        <button type="submit">Update Password</button>
    </form>
</body>
</html>
