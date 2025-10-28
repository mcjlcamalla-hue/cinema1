<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle sending notifications logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    // Logic to send notifications (e.g., email, SMS, etc.)
    // Add success or error handling here
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Notifications</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #242872 0%, #3940c2 50%, #ac2b53 100%);
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
            padding: 20px;
        }
        h1 {
            color: #2e3192;
            text-align: center;
        }
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: none;
            font-size: 14px;
            margin-bottom: 20px;
        }
        button {
            background-color: #ffcc00;
            color: #000;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #ff9900;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Send Notifications</h1>
        <!-- Form for sending notifications -->
        <form method="POST" action="send_notifications.php">
            <textarea name="message" placeholder="Enter your message" required></textarea>
            <button type="submit">Send Notification</button>
        </form>
    </div>
</body>
</html>