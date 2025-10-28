<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle add, edit, delete movie logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'])) {
        // Add new movie
        $title = $conn->real_escape_string($_POST['title']);
        $genre = $conn->real_escape_string($_POST['genre']);
        $duration = $conn->real_escape_string($_POST['duration']);
        $description = $conn->real_escape_string($_POST['description']);
        $cast = $conn->real_escape_string($_POST['cast']);
        $trailer = $conn->real_escape_string($_POST['trailer']);

        // Handle file upload for poster
        $poster = $_FILES['poster']['name'];
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        $target_file = $target_dir . basename($poster);
        move_uploaded_file($_FILES['poster']['tmp_name'], $target_file);

        // Insert movie into the database
        $conn->query("INSERT INTO movies (title, genre, duration, description, cast, poster, trailer) VALUES ('$title', '$genre', '$duration', '$description', '$cast', '$poster', '$trailer')");
    }

    if (isset($_POST['delete'])) {
        // Delete movie
        $movie_id = $conn->real_escape_string($_POST['movie_id']);
        $conn->query("DELETE FROM movies WHERE movie_id = '$movie_id'");
    }

    // You can add edit functionality here as needed
}

// Fetch movies from the database to display
$movies = $conn->query("SELECT * FROM movies");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Movies</title>
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
        input[type="text"], input[type="url"], textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 300px;
            margin: 10px 0;
        }
        input[type="file"] {
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
        <h1>Manage Movies</h1>
        <!-- Form for adding movies -->
        <form method="POST" action="manage_movies.php" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Movie Title" required>
            <input type="text" name="genre" placeholder="Genre" required>
            <input type="text" name="duration" placeholder="Duration" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="text" name="cast" placeholder="Cast" required>
            <input type="file" name="poster" required>
            <input type="url" name="trailer" placeholder="Trailer Link">
            <button type="submit">Add Movie</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Movie ID</th>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Duration</th>
                    <th>Description</th>
                    <th>Cast</th>
                    <th>Poster</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display movies
                while ($movie = $movies->fetch_assoc()) {
                    echo "<tr>
                            <td>{$movie['movie_id']}</td>
                            <td>{$movie['title']}</td>
                            <td>{$movie['genre']}</td>
                            <td>{$movie['duration']}</td>
                            <td>{$movie['description']}</td>
                            <td>{$movie['cast']}</td>
                            <td><img src='uploads/{$movie['poster']}' alt='Poster' width='50'></td>
                            <td class='action-buttons'>
                                <form method='POST' action='manage_movies.php' style='display:inline;'>
                                    <input type='hidden' name='movie_id' value='{$movie['movie_id']}'>
                                    <button type='submit' name='delete' class='delete-button'>Delete</button>
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