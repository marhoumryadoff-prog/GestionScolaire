<?php
// send_test_mail.php (drop in project root)
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$to = 'you@yourdomain.com'; // replace with your email for testing
$subject = 'TEST email from PHPMailer';
$body = "<p>This is a test. If you get this the SMTP is working.</p>";

// SMTP configuration from environment or hardcode temporarily for testing
$host = getenv('MAILER_HOST') ?: 'smtp.example.com';
$user = getenv('MAILER_USER') ?: 'smtp_user';
$pass = getenv('MAILER_PASS') ?: 'smtp_pass';
$port = getenv('MAILER_PORT') ?: 587;
$secure = getenv('MAILER_SECURE') ?: 'tls';
$from = getenv('MAILER_FROM') ?: 'noreply@example.com';
$fromName = getenv('MAILER_FROM_NAME') ?: 'Test';

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // very verbose debug
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $pass;
    $mail->SMTPSecure = $secure;
    $mail->Port = $port;

    $mail->setFrom($from, $fromName);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $body;

    $mail->send();
    echo "\nOK: message sent\n";
} catch (Exception $e) {
    echo "\nPHPMailer Exception: " . $e->getMessage() . "\n";
}