<?php
require __DIR__ . '/vendor/autoload.php';
require 'db.php'; // must provide $conn and SMTP constants (SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, FROM_EMAIL, FROM_NAME)

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Convert millimeters to points (Dompdf uses points: 1pt = 1/72in, 1mm ≈ 2.83465pt)
 */
function mm_to_pt(float $mm): float {
    return $mm * 2.8346456693;
}

/**
 * Build a simple wallet-size ticket HTML (85x55 mm) from data array.
 * $data keys: movie_title, username, seat, showtime, amount, booking_id
 */
function buildWalletHtml(array $data): string {
    $movie = htmlspecialchars($data['movie_title'] ?? 'Movie');
    $name = htmlspecialchars($data['username'] ?? '');
    $seat = htmlspecialchars($data['seat'] ?? '');
    $time = htmlspecialchars($data['showtime'] ?? '');
    $amount = number_format((float)($data['amount'] ?? 0), 2);
    $bid = htmlspecialchars($data['booking_id'] ?? '');

    // Inline CSS sized for small ticket; Dompdf will render using mm-sized page
    return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { size: 85mm 55mm; margin: 4mm; }
    body { font-family: Arial, Helvetica, sans-serif; color: #fff; background: #12121e; margin:0; padding:4mm; }
    .wrap { width:100%; height:100%; box-sizing:border-box; display:flex; flex-direction:column; justify-content:space-between; }
    .header { text-align:center; }
    .title { color:#f1c40f; font-weight:700; font-size:14px; margin-bottom:2px; }
    .movie { color:#ffffff; font-weight:700; font-size:11px; margin-bottom:2px; }
    .info { display:flex; justify-content:space-between; font-size:9px; color:#dcdcdc; margin-top:6px; }
    .info .left { max-width:60%; }
    .amount { text-align:center; color:#f1c40f; font-weight:700; font-size:11px; margin-top:6px; }
    .footer { text-align:center; font-size:6.5px; color:#bdbdbd; margin-top:6px; }
    .badge { display:inline-block; padding:4px 6px; border-radius:4px; background:rgba(255,255,255,0.06); font-size:8px; color:#fff; }
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="title">Cinema Ticket</div>
        <div class="movie">{$movie}</div>
    </div>

    <div class="info">
        <div class="left">
            <div class="badge">Name: {$name}</div><br>
            <div style="margin-top:6px; font-size:9px;">Seat: <strong>{$seat}</strong></div>
        </div>
        <div class="right" style="text-align:right;">
            <div style="font-size:9px;">Time</div>
            <div style="font-weight:700; margin-top:4px;">{$time}</div>
        </div>
    </div>

    <div class="amount">₱ {$amount}</div>

    <div class="footer">
        Booking ID: {$bid} — Present this ticket (printed or digital) at the entrance. Non-transferable.
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Render HTML to a small wallet-size PDF and return PDF binary string.
 * Uses Dompdf with custom page size (85 x 55 mm).
 */
function renderWalletPdf(string $html): string {
    $dompdf = new Dompdf();
    // ensure the HTML includes @page size or set paper explicitly in points
    $widthPt = mm_to_pt(85.0);
    $heightPt = mm_to_pt(55.0);
    $dompdf->setPaper([ $widthPt, $heightPt ]);
    $dompdf->loadHtml($html);
    $dompdf->render();
    return $dompdf->output();
}

/**
 * Build PDF and send email with PDF attached.
 * Returns true on success, false on failure.
 */
function sendTicketEmail(string $toEmail, string $toName, string $htmlContent): bool {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("sendTicketEmail: invalid email '{$toEmail}'");
        return false;
    }

    // If caller provided empty htmlContent, do not proceed.
    if (empty($htmlContent)) {
        error_log('sendTicketEmail: empty html content');
        return false;
    }

    // Render wallet-size PDF
    try {
        $pdfString = renderWalletPdf($htmlContent);
    } catch (\Exception $e) {
        error_log('sendTicketEmail: PDF render failed: ' . $e->getMessage());
        return false;
    }

    // SMTP configuration: prefer constants defined in db.php or config.php
    $smtpHost = defined('SMTP_HOST') ? SMTP_HOST : null;
    $smtpUser = defined('SMTP_USER') ? SMTP_USER : null;
    $smtpPass = defined('SMTP_PASS') ? SMTP_PASS : null;
    $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $fromEmail = defined('FROM_EMAIL') ? FROM_EMAIL : null;
    $fromName  = defined('FROM_NAME') ? FROM_NAME : 'Cinema Booking';

    if (!$smtpHost || !$smtpUser || !$smtpPass || !$fromEmail) {
        error_log('sendTicketEmail: SMTP configuration missing (set SMTP_HOST/SMTP_USER/SMTP_PASS/FROM_EMAIL).');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your Cinema Ticket';
        // Use the provided HTML as the email body (a trimmed version)
        $mail->Body    = 'Thank you for your booking. Your ticket is attached as a PDF.<br><br>' .
                         '<div style="font-family:Arial,sans-serif;font-size:13px;color:#333;">' .
                         strip_tags(substr($htmlContent, 0, 1000)) . '</div>';

        // Attach the PDF (as a string)
        $mail->addStringAttachment($pdfString, 'ticket_wallet.pdf', 'base64', 'application/pdf');

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('sendTicketEmail error: ' . $mail->ErrorInfo . ' Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Lookup the user's email by username and send ticket.
 * If $htmlContent is empty, build wallet HTML from booking record.
 *
 * $username: registered username
 * $bookingId: numeric id for bookings table
 * $htmlContent: HTML used to render PDF (if empty, function will build one)
 */
function sendTicketToUser(string $username, $bookingId, string $htmlContent = ''): bool {
    global $conn;
    if (empty($username)) {
        error_log('sendTicketToUser: empty username');
        return false;
    }
    // fetch email and optionally full name
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE username = ?");
    if (!$stmt) {
        error_log('sendTicketToUser prepare failed: ' . $conn->error);
        return false;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $stmt->close();
        error_log("sendTicketToUser: user '{$username}' not found");
        return false;
    }
    $stmt->bind_result($email, $userName);
    $stmt->fetch();
    $stmt->close();

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("sendTicketToUser: invalid email for user '{$username}': '{$email}'");
        return false;
    }

    // If htmlContent empty, build it using booking data
    if (empty($htmlContent)) {
        // fetch booking
        $bid = (int)$bookingId;
        $bkStmt = $conn->prepare("SELECT b.*, COALESCE(m.title, '') AS movie_title FROM bookings b LEFT JOIN movies m ON b.movie_id = m.movie_id WHERE b.id = ? LIMIT 1");
        if (!$bkStmt) {
            error_log('sendTicketToUser booking prepare failed: ' . $conn->error);
            return false;
        }
        $bkStmt->bind_param('i', $bid);
        $bkStmt->execute();
        $res = $bkStmt->get_result();
        $booking = $res ? $res->fetch_assoc() : null;
        $bkStmt->close();
        if (!$booking) {
            error_log("sendTicketToUser: booking {$bid} not found");
            return false;
        }

        $data = [
            'movie_title' => $booking['movie_title'] ?: ($booking['movie_id'] ?? 'Movie'),
            'username'    => $booking['username'] ?? $userName,
            'seat'        => $booking['seat'] ?? '',
            'showtime'    => $booking['showtime'] ?? '',
            'amount'      => $booking['amount'] ?? 0,
            'booking_id'  => $booking['id'] ?? $bid,
        ];
        $htmlContent = buildWalletHtml($data);
    }

    return sendTicketEmail($email, $userName ?: $username, $htmlContent);
}