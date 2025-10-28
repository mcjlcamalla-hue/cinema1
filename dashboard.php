<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #242872 0%, #3940c2 50%, #ac2b53 100%);
            margin: 0;
            padding: 0;
            color: #333;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure the body takes full height */
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
            flex: 1; /* Allow the container to grow */
        }
        .sidebar {
            width: 250px;
            background: #2e3192;
            color: white;
            padding: 20px;
            border-radius: 12px 0 0 12px;
        }
        .sidebar h2 {
            color: #ffcc00;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin: 5px 0;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #ff9900;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        h1 {
            color: #2e3192;
        }
        footer {
            text-align: center; /* Center the text */
            margin-top: auto; /* Push footer to the bottom */
            font-size: 12px;
            color: #666;
            padding: 10px 0; /* Add some padding */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Menu</h2>
            <a href="manage_movies.php">Manage Movies</a>
            <a href="manage_cinemas.php">Manage Cinemas</a>
            <a href="manage_showtimes.php">Showtime Scheduling</a>
            <a href="manage_seats.php">Seat Management</a>
            <a href="manage_bookings.php">Booking Management</a>
            <a href="manage_users.php">User Management</a>
            <a href="manage_payments.php">Payment Reports</a>
            <a href="send_notifications.php">Notifications</a>
            <a href="admin_logout.php">Logout</a>
        </div>
        <div class="content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h1>
            <p>Select an option from the menu to manage the cinema system.</p>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Cinema Management System. All rights reserved.</p>
    </footer>
</body>
</html>
