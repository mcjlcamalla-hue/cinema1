<?php
session_start();
include("db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// verify payments table exists
$hasPayments = $conn->query("SHOW TABLES LIKE 'payments'");
if (!$hasPayments || $hasPayments->num_rows === 0) {
    // show a friendly admin message instead of a fatal error
    $payments = false;
    $tableError = "The 'payments' table does not exist. Create it using the SQL provided.";
} else {
    // safe SELECT with LEFT JOIN (users may use id or user_id; try both)
    // determine users PK name
    $userKey = 'user_id';
    $uSample = $conn->query("SELECT * FROM users LIMIT 1");
    if ($uSample) {
        $userCols = array_map(function($f){ return $f->name; }, $uSample->fetch_fields());
        if (in_array('user_id', $userCols)) $userKey = 'user_id';
        elseif (in_array('id', $userCols)) $userKey = 'id';
        $uSample->free();
    } else {
        $userKey = 'user_id';
    }

    // fetch payments with username when possible
    $payments = $conn->query("
        SELECT p.payment_id, u.username, p.amount, p.status, p.date
        FROM payments p
        LEFT JOIN users u ON p.user_id = u.$userKey
        ORDER BY p.date DESC
    ");

    if (!$payments) {
        $tableError = "Database query failed: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Payments</title>
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
        .refund-button {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background-color: #e74c3c;
            color: white;
            transition: background 0.3s;
        }
        .refund-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Payments</h1>

        <?php if (isset($tableError)): ?>
            <div style="padding:12px;border-radius:6px;background:#fff3f3;color:#900;margin-bottom:16px;">
                <?php echo htmlspecialchars($tableError); ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($payments && $payments->num_rows > 0) {
                    while ($payment = $payments->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($payment['payment_id']) . "</td>
                                <td>" . htmlspecialchars($payment['username'] ?? '') . "</td>
                                <td>" . htmlspecialchars($payment['amount']) . "</td>
                                <td>" . htmlspecialchars($payment['status']) . "</td>
                                <td>" . htmlspecialchars($payment['date']) . "</td>
                                <td class='action-buttons'>
                                    <form method='POST' action='refund_payment.php' style='display:inline;'>
                                        <input type='hidden' name='payment_id' value='" . htmlspecialchars($payment['payment_id']) . "'>
                                        <button type='submit' class='refund-button'>Refund</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } elseif (!isset($tableError)) {
                    echo "<tr><td colspan='6'>No payments found.</td></tr>";
                } else {
                    echo "<tr><td colspan='6'>No data to display.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>