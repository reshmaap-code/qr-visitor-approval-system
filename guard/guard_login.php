<?php
session_start();
include '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM guard_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['guard_logged_in'] = true;
            $_SESSION['guard_username'] = $row['username'];
            header("Location: guard_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Guard user not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🛡️ Guard Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reuse visitor form look */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #deebf7, #b6d7f2);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-box {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 30px;
            font-size: 26px;
            font-weight: 700;
            border-bottom: 2px solid #003366;
            padding-bottom: 10px;
        }

        label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 2px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #4dd7e9ff;
            background: #fff;
            box-shadow: 0 0 5px rgba(102, 166, 255, 0.3);
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        button {
            width: 100%;
            background: linear-gradient(to right, #003366, #0066cc);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        button:hover {
            background: linear-gradient(to right, #0066cc, #003366);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.4);
        }

        .show-password {
            font-size: 13px;
            color: #0066cc;
            cursor: pointer;
            user-select: none;
            margin-top: -10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .forgot {
            text-align: center;
            margin-top: 15px;
        }

        .forgot a {
            color: #003366;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2>🛡️ Guard Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" placeholder="Enter username" required>

        <label>Password:</label>
        <input type="password" name="password" id="passwordInput" placeholder="Enter password" required>

        <div class="show-password" onclick="togglePassword()">👁 Show Password</div>

        <button type="submit">Login</button>
    </form>

    <div class="forgot">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const toggle = document.querySelector('.show-password');
    if (input.type === 'password') {
        input.type = 'text';
        toggle.textContent = "🙈 Hide Password";
    } else {
        input.type = 'password';
        toggle.textContent = "👁 Show Password";
    }
}
</script>
</body>
</html>
