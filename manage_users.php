<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT id, username, role FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #242872 0%, #3940c2 50%, #ac2b53 100%);
            margin: 0;
            padding: 0;
            background-attachment: fixed;
        }
        .container {
            width: 85%;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 6px 15px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #2e3192;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #2e3192;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        a.btn {
            background: #ff3333;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }
        a.btn:hover {
            background: #cc0000;
        }
        .back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 15px;
            background: #ffcc00;
            color: black;
            border-radius: 6px;
            text-decoration: none;
        }
        .back:hover {
            background: #ff9900;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn"
                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
