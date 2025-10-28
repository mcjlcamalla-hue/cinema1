<?php
header('Content-Type: application/json');
require 'db.php'; // optional: reuse your DB/session config

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$gender = $_POST['gender'] ?? '';
$email = trim($_POST['email'] ?? '');

if (!$username || !$password || !$confirm || !$email) {
    echo json_encode(['success'=>false,'message'=>'All fields are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'message'=>'Invalid email address.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters.']);
    exit;
}
if ($password !== $confirm) {
    echo json_encode(['success'=>false,'message'=>'Passwords do not match.']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

// ensure $conn is set (from config.php) or create a new mysqli here
// using $conn from config.php recommended
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success'=>false,'message'=>'Username or email already taken.']);
    $stmt->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO users (username,password,gender,email,role) VALUES (?, ?, ?, ?, 'user')");
$stmt->bind_param("ssss", $username, $hashed, $gender, $email);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Registration failed.']);
}
$stmt->close();
$conn->close();
exit;
?>

