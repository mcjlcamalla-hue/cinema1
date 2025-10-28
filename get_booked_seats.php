<?php
header('Content-Type: application/json');

// Get parameters as text
$movie = isset($_GET['movie']) ? $_GET['movie'] : '';
$cinema = isset($_GET['cinema']) ? $_GET['cinema'] : '';
$showtime = isset($_GET['showtime']) ? $_GET['showtime'] : '';

// Convert indexes to text if needed
$movies = ["A Minecraft Movie", "Snow White", "My Love Make You Disappear", "Kaiju no.8"];
$cinemaTypes = ["Standard Cinema", "IMAX Cinema", "Director's Club"];
$showTimes = [
  ["10:00 AM", "1:00 PM", "4:00 PM"],
  ["11:00 AM", "2:00 PM", "5:00 PM"],
  ["12:00 PM", "3:00 PM", "6:00 PM"],
  ["10:30 AM", "1:30 PM", "4:30 PM"]
];

if (is_numeric($movie)) $movie = $movies[intval($movie)];
if (is_numeric($cinema)) $cinema = $cinemaTypes[intval($cinema)];
if (is_numeric($showtime)) $showtime = $showTimes[array_search($movie, $movies)][intval($showtime)];

$conn = new mysqli('localhost', 'root', '', 'cinemadb');
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT seat FROM bookings WHERE movie_id=? AND cinema_type=? AND showtime=?");
$stmt->bind_param("sss", $movie, $cinema, $showtime);
$stmt->execute();
$result = $stmt->get_result();

$seats = [];
while ($row = $result->fetch_assoc()) {
    $seats[] = $row['seat'];
}
$stmt->close();
$conn->close();

echo json_encode($seats);
?>