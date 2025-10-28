<?php
$host = "localhost";   // database host
$user = "root";        // database username
$pass = "";            // database password
$db   = "cinemadb";    // your database name

// ✅ Avoid redefining constants
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USER')) define('SMTP_USER', 'mcjlcamalla@tip.edu.ph');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'rbuw idnr riwr ebuu');
if (!defined('FROM_EMAIL')) define('FROM_EMAIL', 'mcjlcamalla@tip.edu.ph');
if (!defined('FROM_NAME')) define('FROM_NAME', 'Cinema Booking');

// ✅ Create DB connection
$conn = new mysqli($host, $user, $pass, $db);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
