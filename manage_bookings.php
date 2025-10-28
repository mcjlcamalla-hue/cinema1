<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

    // Fetch bookings using the correct ID column name
$sql = "SELECT * FROM bookings ORDER BY id DESC";
$bookings = $conn->query($sql);

// Handle success/error messages
$message = '';
if (isset($_GET['deleted'])) {
    $message = '<div class="alert success">Booking deleted successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert error">Error processing request.</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{font-family:Segoe UI,Arial;margin:0;padding:0;background:linear-gradient(120deg,#242872,#ac2b53);color:#333;background-attachment: fixed;} 
        .container{width:85%;max-width:1200px;margin:50px auto;background:#fff;border-radius:12px;box-shadow:0 6px 15px rgba(0,0,0,.2);padding:20px}
        h1{color:#2e3192;text-align:center}
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{padding:12px;text-align:left;border-bottom:1px solid #eee}
        th{background:#f7f7f7}
        .action-buttons{display:flex;gap:8px}
        .edit-button{background:#ffcc00;padding:6px;border-radius:6px;border:0}
        .cancel-button{background:#e74c3c;padding:6px;border-radius:6px;border:0;color:#fff}
        .btn { 
            display: inline-block;
            padding: 8px 16px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-edit { background: #ffd700; color: #000; }
        .btn-delete { background: #ff4444; color: #fff; }
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Bookings</h1>
    
    <?php echo $message; ?>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>User</th>
                <th>Movie</th>
                <th>Showtime</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings && $bookings->num_rows > 0): ?>
                <?php while($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                        <td><?php echo htmlspecialchars($booking['movie_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['showtime']); ?></td>
                        <td>
                            <a href="edit_booking.php?id=<?php echo $booking['id']; ?>" 
                               class="btn btn-edit">Edit</a>
                            
                            <form method="post" action="delete_booking.php" style="display:inline">
                                <input type="hidden" name="id" 
                                       value="<?php echo $booking['id']; ?>">
                                <button type="submit" class="btn btn-delete" 
                                        onclick="return confirm('Are you sure you want to delete booking #<?php echo $booking['id']; ?>?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No bookings found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>