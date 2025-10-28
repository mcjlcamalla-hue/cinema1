<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle add, edit, delete showtime logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['movie_id'], $_POST['cinema_id'], $_POST['showtime'])) {
        $movie_id = $conn->real_escape_string($_POST['movie_id']);
        $cinema_id = $conn->real_escape_string($_POST['cinema_id']);
        $showtime = $conn->real_escape_string($_POST['showtime']);

        // Insert new showtime into the database
        $conn->query("INSERT INTO showtimes (movie_id, cinema_id, showtime) VALUES ('$movie_id', '$cinema_id', '$showtime')");
    }
}

// Fetch showtimes from the database to display
$showtimes = $conn->query("SELECT s.showtime_id, m.title AS movie_title, c.cinema_name, s.showtime 
                            FROM showtimes s 
                            JOIN movies m ON s.movie_id = m.movie_id 
                            JOIN cinemas c ON s.cinema_id = c.cinema_id");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Showtimes</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS -->
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
            flex-direction: column;
            align-items: center;
        }
        select, input[type="datetime-local"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 300px;
            margin: 10px 0;
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
        <h1>Manage Showtimes</h1>
        <!-- Form for adding showtimes -->
        <form method="POST" action="manage_showtimes.php">
            <select name="movie_id" required>
                <option value="">Select Movie</option>
                <!-- Populate with movies -->
                <?php
                $movies = $conn->query("SELECT movie_id, title FROM movies");
                while ($movie = $movies->fetch_assoc()) {
                    echo "<option value='{$movie['movie_id']}'>{$movie['title']}</option>";
                }
                ?>
            </select>
            <select name="cinema_id" required>
                <option value="">Select Cinema</option>
                <!-- Populate with cinemas -->
                <?php
                $cinemas = $conn->query("SELECT cinema_id, cinema_name FROM cinemas");
                while ($cinema = $cinemas->fetch_assoc()) {
                    echo "<option value='{$cinema['cinema_id']}'>{$cinema['cinema_name']}</option>";
                }
                ?>
            </select>
            <input type="datetime-local" name="showtime" required>
            <button type="submit">Add Showtime</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Showtime ID</th>
                    <th>Movie</th>
                    <th>Cinema</th>
                    <th>Showtime</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display showtimes
                while ($showtime = $showtimes->fetch_assoc()) {
                    echo "<tr>
                            <td>{$showtime['showtime_id']}</td>
                            <td>{$showtime['movie_title']}</td>
                            <td>{$showtime['cinema_name']}</td>
                            <td>{$showtime['showtime']}</td>
                            <td class='action-buttons'>
                                <form method='POST' action='delete_showtime.php' style='display:inline;'>
                                    <input type='hidden' name='showtime_id' value='{$showtime['showtime_id']}'>
                                    <button type='submit' class='delete-button'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>