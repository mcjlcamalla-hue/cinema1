<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cinema Ticketing</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div id="app">
    <nav id="nav">
        <button onclick="showPanel('movies')">View Movies</button>
        <button onclick="showPanel('book')">Book Ticket</button>
        <button onclick="showPanel('food')">Food Selection</button>
        <button onclick="showPanel('payment')">Payment</button>
        <button onclick="showPanel('history')">Booking History</button>
        <form method="POST" action="logout.php" style="display:inline;">
            <button type="submit" style="background:#e74c3c;">Logout</button>
        </form>
    </nav>

    <section id="welcome-panel">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div id="about-us" style="margin: 32px 0;">
            <h2>ABOUT US</h2>
            <h3>Who We Are</h3>
            <p>
                Welcome to our Movie Booking website! 🎬 Our mission is to bring you the best cinema experience by making it easy to book tickets, select seats, and enjoy the latest movies hassle-free.
            </p>
            <h3>Our Vision</h3>
            <p>
                We aim to be your go-to online platform for quick and secure movie booking. With options from Standard Cinema, IMAX, to Director’s Club, we ensure there’s something for everyone!
            </p>
            <h3>Why Choose Us?</h3>
            <ul>
                <li>Fast and secure booking system</li>
                <li>Wide variety of cinema formats</li>
                <li>Easy seat availability checking</li>
                <li>Safe online payments</li>
            </ul>
        </div>
    </section>

    <section id="movies-panel" class="hidden"></section>
    <section id="book-panel" class="hidden"></section>
    <section id="food-panel" class="hidden"></section>
    <section id="payment-panel" class="hidden"></section>
</div>
<script src="app.js"></script>
</body>
</html>