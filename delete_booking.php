<?php
session_start();
include('db.php');
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: manage_bookings.php'); exit; }
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: manage_bookings.php'); exit; }

$update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? LIMIT 1");
$update->bind_param("i", $id);
$update->execute();
$update->close();

header('Location: manage_bookings.php');
exit;
?>
