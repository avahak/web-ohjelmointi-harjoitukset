<?php

// Using https://github.com/PHPMailer/PHPMailer and https://mailtrap.io/ 
// for sending test emails

require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer; 

require_once __DIR__ . '/../../../config/mailtrap.php';
require_once __DIR__ . '/../../../logs/logger.php';

$logger = new Logger();

function mailtrap_send($subject, $body, $my_name, $recipient_email, $recipient_name, $isHTML=false) {
    global $logger;
    $mail = new PHPMailer();

    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = MAILTRAP_IO_USERNAME;
    $mail->Password = MAILTRAP_IO_PASSWORD;
    $mail->SMTPSecure = 'tls'; // Use 'tls' or 'ssl' depending on your server configuration
    $mail->Port = 2525;
    $mail->CharSet = "UTF-8";   // enables ä, ö

    $mail->setFrom(MAILTRAP_IO_EMAIL, $my_name);
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->Subject = $subject;
    if ($isHTML)
        $mail->isHTML(true);
    $mail->Body = $body;

    // $mail->addAttachment('path/to/attachment.pdf');

    if ($mail->send()) {
        $logger->info("Email sent.", ["subject" => $subject, "to" => $recipient_email]);
    } else {
        $logger->info("Email send failed.", ["to" => $recipient_email, "msg" => $mail->ErrorInfo]);
    }
}

// mailtrap_send("Test subject", "Test body", "Webteam", "test@testaus.com", "testii");

?>