<?php
session_start();
include 'db.php';

// Handle admin login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid admin username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Cinema</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #23243a, #3a3b58);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #login-panel {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            width: 350px;
            text-align: center;
        }

        #login-panel h2 {
            margin-bottom: 20px;
            color: #23243a;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #f1c40f;
            color: #23243a;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #e1b70e;
        }

        .msg {
            margin-top: 10px;
            color: red;
            font-size: 14px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 15px;
            font-size: 14px;
            text-decoration: none;
            color: #23243a;
        }
    </style>
</head>
<body>
    <section id="login-panel">
        <h2>Admin Login</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($error)) echo "<div class='msg'>$error</div>"; ?>
        </form>
        <a href="index.html" class="back-btn">← Back to Customer Login</a>
    </section>
</body>
</html>
