<?php
session_start();
include("db.php");

// ✅ 1. Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ✅ 2. Check if "id" parameter is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // ✅ 3. Use prepared statement for security
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // ✅ 4. User deleted successfully
        header("Location: manage_users.php?status=deleted");
        exit();
    } else {
        // ❌ Failed to delete
        header("Location: manage_users.php?status=error");
        exit();
    }
} else {
    // ❌ Invalid request
    header("Location: manage_users.php?status=invalid");
    exit();
}
?>
