<?php
session_start();
header('Content-Type: application/json');
$user = $_SESSION['username'] ?? '';
$conn = new mysqli('localhost', 'root', '', 'cinemadb');
if ($conn->connect_error) { echo json_encode([]); exit; }
$stmt = $conn->prepare("SELECT movie_id, cinema_type, showtime, seat FROM bookings WHERE username=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
?>