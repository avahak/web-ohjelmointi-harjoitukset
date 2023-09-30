<?php

// Using https://github.com/PHPMailer/PHPMailer
// Using https://mailtrap.io/ for testing 

require_once '../config/mailtrap.php';
require_once '../logs/logger.php';
require_once '../include/PHPMailer/src/Exception.php';
require_once '../include/PHPMailer/src/PHPMailer.php';
require_once '../include/PHPMailer/src/SMTP.php';

$logger = new Logger();

// use PHPMailer\PHPMailer\PHPMailer; 
// use PHPMailer\PHPMailer\Exception;

function send_mail($subject, $body, $my_name, $recipient_email, $recipient_name, $isHTML=false) {
    global $logger;
    $mail = new PHPMailer\PHPMailer\PHPMailer();

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

// send_mail("Test subject", "Test body", "Webteam", "test@testaus.com", "testii");

?>