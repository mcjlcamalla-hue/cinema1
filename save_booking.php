<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'email_ticket.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required booking data
if (
    empty($data['movie']) ||
    empty($data['cinema']) ||
    empty($data['showtime']) ||
    empty($data['seats']) || !is_array($data['seats'])
) {
    echo json_encode(['success' => false, 'message' => 'Missing booking information']);
    exit;
}

// Get username from session or request
$username = $_SESSION['username'] ?? $data['username'] ?? null;
if (!$username) {
    echo json_encode(['success' => false, 'message' => 'No user logged in or specified']);
    exit;
}

// Get user email
$stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($userEmail);
$stmt->fetch();
$stmt->close();

if (!$userEmail) {
    echo json_encode(['success' => false, 'message' => 'User email not found']);
    exit;
}

// Save bookings (1 row per seat)
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO bookings (username, movie_id, cinema_type, showtime, seat) VALUES (?, ?, ?, ?, ?)");
    foreach ($data['seats'] as $seat) {
        $seat = trim($seat);
        $stmt->bind_param("sssss", $username, $data['movie'], $data['cinema'], $data['showtime'], $seat);
        $stmt->execute();
    }
    $stmt->close();
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
    exit;
}

// Prepare ticket email
$seatsHtml = htmlspecialchars(implode(', ', $data['seats']));
$foodsHtml = '';

if (!empty($data['foods']) && is_array($data['foods'])) {
    $foodsHtml = '<ul>';
    foreach ($data['foods'] as $f) {
        $name = htmlspecialchars($f['name'] ?? '');
        $qty = intval($f['qty'] ?? 0);
        if ($name && $qty > 0) {
            $foodsHtml .= "<li>$name x $qty</li>";
        }
    }
    $foodsHtml .= '</ul>';
} else {
    $foodsHtml = '<p>None</p>';
}

$ticketHtml = '
<html><body>
  <h2>Ticket - ' . htmlspecialchars($data['movie']) . '</h2>
  <p><strong>Cinema:</strong> ' . htmlspecialchars($data['cinema']) . '</p>
  <p><strong>Showtime:</strong> ' . htmlspecialchars($data['showtime']) . '</p>
  <p><strong>Seats:</strong> ' . $seatsHtml . '</p>
  <h3>Food</h3>
  ' . $foodsHtml . '
</body></html>
';

$sent = sendTicketEmail($userEmail, $username, $ticketHtml);

// Respond
if ($sent) {
    echo json_encode(['success' => true, 'message' => 'Booking saved and emailed']);
} else {
    echo json_encode(['success' => true, 'message' => 'Booking saved but email failed']);
}
?>
