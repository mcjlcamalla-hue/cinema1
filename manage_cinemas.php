<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle add, edit, delete cinema logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cinema_name'])) {
        // Add new cinema
        $cinema_name = $conn->real_escape_string($_POST['cinema_name']);
        $conn->query("INSERT INTO cinemas (cinema_name) VALUES ('$cinema_name')");
    }
}

// Fetch cinemas from the database to display
$cinemas = $conn->query("SELECT * FROM cinemas");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Cinemas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #242872 0%, #3940c2 50%, #ac2b53 100%);
            margin: 0;
            padding: 0;
            color: #333;
            background-attachment: fixed;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
            padding: 20px;
        }
        h1 {
            color: #2e3192;
            text-align: center;
        }
        form {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 300px;
            margin-right: 10px;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background-color: #ffcc00;
            color: #000;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #ff9900;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            justify-content: space-around;
        }
        .edit-button, .delete-button {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .edit-button {
            background-color: #ffcc00;
            color: #000;
        }
        .edit-button:hover {
            background-color: #ff9900;
        }
        .delete-button {
            background-color: #e74c3c;
            color: white;
        }
        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Cinemas</h1>
        <!-- Form for adding cinemas -->
        <form method="POST" action="manage_cinemas.php">
            <input type="text" name="cinema_name" placeholder="Cinema Name" required>
            <button type="submit">Add Cinema</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Cinema ID</th>
                    <th>Cinema Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display cinemas
                while ($cinema = $cinemas->fetch_assoc()) {
                    echo "<tr>
                            <td>{$cinema['cinema_id']}</td>
                            <td>{$cinema['cinema_name']}</td>
                            <td class='action-buttons'>
                                <form method='POST' action='manage_cinemas.php' style='display:inline;'>
                                    <input type='hidden' name='cinema_id' value='{$cinema['cinema_id']}'>
                                    <button type='submit' name='edit' class='edit-button'>Edit</button>
                                </form>
                                <form method='POST' action='manage_cinemas.php' style='display:inline;'>
                                    <input type='hidden' name='cinema_id' value='{$cinema['cinema_id']}'>
                                    <button type='submit' name='delete' class='delete-button'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Handle edit and delete actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete'])) {
            $cinema_id = $conn->real_escape_string($_POST['cinema_id']);
            $conn->query("DELETE FROM cinemas WHERE cinema_id = '$cinema_id'");
            header("Location: manage_cinemas.php"); // Refresh the page
            exit();
        }

        if (isset($_POST['edit'])) {
            // Logic for editing cinemas can be added here
            // You may want to fetch the cinema details and show them in a form for editing
        }
    }
    ?>
</body>
</html>