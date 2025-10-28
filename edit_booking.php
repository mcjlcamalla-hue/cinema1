<?php
session_start();
include('db.php');
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $username = $_POST['username'] ?? '';
    $movie_id = $_POST['movie_id'] ?? null;
    $showtime = $_POST['showtime'] ?? '';
    $seat = $_POST['seat'] ?? '';
    $amount = (float)($_POST['amount'] ?? 0);
    $stmt = $conn->prepare("UPDATE bookings SET username=?, movie_id=?, showtime=?, seat=?, amount=? WHERE id=?");
    $stmt->bind_param("sissdi",$username,$movie_id,$showtime,$seat,$amount,$id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_bookings.php'); exit;
}

$row = null;
if ($id > 0) {
    $res = $conn->query("SELECT * FROM bookings WHERE id = $id LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Edit Booking</title></head>
<body>
<?php if (!$row): echo "Booking not found"; exit; endif; ?>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <label>User <input name="username" value="<?php echo htmlspecialchars($row['username']); ?>"></label><br>
    <label>Movie ID/Title <input name="movie_id" value="<?php echo htmlspecialchars($row['movie_id'] ?? $row['movie_title'] ?? ''); ?>"></label><br>
    <label>Showtime <input name="showtime" value="<?php echo htmlspecialchars($row['showtime']); ?>"></label><br>
    <label>Seat <input name="seat" value="<?php echo htmlspecialchars($row['seat']); ?>"></label><br>
    <label>Amount <input name="amount" type="number" step="0.01" value="<?php echo htmlspecialchars($row['amount'] ?? 0); ?>"></label><br>
    <button type="submit">Save</button>
</form>
</body>
</html>