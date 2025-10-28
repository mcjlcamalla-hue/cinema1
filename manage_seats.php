<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle seat management logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['selected_seats'])) {
        $selectedSeats = json_decode($_POST['selected_seats'], true); // decode JSON array
        if (json_last_error() === JSON_ERROR_NONE && is_array($selectedSeats)) {
            // Example: reset all seats then mark selected as occupied.
            // Scope by hall_id if you have halls (add WHERE hall_id = ?)
            $conn->begin_transaction();
            $conn->query("UPDATE seats SET occupied = 0");
            $stmt = $conn->prepare("UPDATE seats SET occupied = 1 WHERE seat_id = ?");
            foreach ($selectedSeats as $seatId) {
                $sid = (int)$seatId;
                $stmt->bind_param("i", $sid);
                $stmt->execute();
            }
            $stmt->close();
            $conn->commit();
            header("Location: manage_seats.php");
            exit();
        } else {
            // bad JSON
            $error = "Invalid seat data received.";
        }
    }
}

// Fetch seat layouts from the database to display
$seats = $conn->query("SELECT seat_id, occupied FROM seats");
if (!$seats) {
    // helpful dev message
    die("Database error: " . $conn->error . " — create the 'seats' table (see README or run SQL).");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Seats</title>
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
        .seat-layout {
            display: grid;
            grid-template-columns: repeat(10, 1fr); /* Adjust number of columns */
            gap: 10px;
            margin: 20px 0;
        }
        .seat {
            width: 40px;
            height: 40px;
            background-color: #28a745; /* Available seat color */
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .seat:hover {
            background-color: #218838; /* Hover effect for available seat */
        }
        .seat.selected {
            background-color: #ffc107; /* Selected seat color */
        }
        .seat.occupied {
            background-color: #dc3545; /* Occupied seat color */
            cursor: not-allowed;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background-color: #ffcc00;
            color: #000;
            cursor: pointer;
            transition: background 0.3s;
            margin: 0 10px;
        }
        button:hover {
            background-color: #ff9900;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Seats</h1>
        <form method="POST" action="manage_seats.php">
            <div class="seat-layout">
                <?php
                // Display seats
                while ($seat = $seats->fetch_assoc()) {
                    $occupiedClass = $seat['occupied'] ? 'occupied' : '';
                    echo "<div class='seat $occupiedClass' data-seat-id='{$seat['seat_id']}'></div>";
                }
                ?>
            </div>
            <div class="action-buttons">
                <button type="submit" id="save-seats">Save Changes</button>
                <button type="button" id="reset-seats">Reset Selection</button>
            </div>
            <input type="hidden" name="selected_seats" id="selected_seats" value="">
        </form>
    </div>
    <script>
        // JavaScript to handle seat selection
        document.querySelectorAll('.seat').forEach(seat => {
            seat.addEventListener('click', function() {
                if (!this.classList.contains('occupied')) {
                    this.classList.toggle('selected');
                }
            });
        });

        document.getElementById('save-seats').addEventListener('click', function() {
            // Logic to save selected seats
            const selectedSeats = [...document.querySelectorAll('.seat.selected')].map(seat => seat.dataset.seatId);
            document.getElementById('selected_seats').value = JSON.stringify(selectedSeats); // Store selected seats in hidden input
        });

        document.getElementById('reset-seats').addEventListener('click', function() {
            document.querySelectorAll('.seat.selected').forEach(seat => {
                seat.classList.remove('selected');
            });
        });
    </script>
</body>
</html>