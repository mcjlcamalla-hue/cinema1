<?php
$host = "localhost";   // database host
$user = "root";        // database username
$pass = "";            // database password
$db   = "cinemadb";    // your database name

// ✅ Avoid redefining constants
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USER')) define('SMTP_USER', '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('FROM_EMAIL')) define('FROM_EMAIL', '');
if (!defined('FROM_NAME')) define('FROM_NAME', 'Cinema Booking');

// ✅ Create DB connection
$conn = new mysqli($host, $user, $pass, $db);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
