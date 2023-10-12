<?php

// Using google OAuth2 (needs Client ID) and PHPMailer to send mail with gmail
// Follow directions in https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2
// and https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail_xoauth.phps
// Steps: 
// 1) Get google gmail account 
// 2) Use https://console.cloud.google.com/ to create a new project
// 3) Enable Gmail API for that project
// 4) Create new (OAuth) Client ID with gmail account from 1) as test user for a web project
//    and a redirect URI "http://127.0.0.1/(path)/get_oauth_token.php".
//    This gives you client ID and client secret keys.
// 5) Run php script in 4) (from PHPMailer) and enter google, client id, and client secret.
//    This gives you refresh token.
// 6) Now that you have Client ID, Client secret, and refresh token, you can send 
//    mail from gmail 1) with this script.

require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../../logs/logger.php';

// Use this if there are problems, see comments in gmail_xoauth.phps from PHPMailer
// date_default_timezone_set('Etc/UTC');

$logger = new Logger();

function get_oauth() {
    $provider = new Google([
        'clientId'     => $GLOBALS["CONFIG"]["GOOGLE_CLIENT_ID"],
        'clientSecret' => $GLOBALS["CONFIG"]["GOOGLE_CLIENT_SECRET"]
    ]);
    $oauth = new OAuth([
        'provider' => $provider,
        'userName' => $GLOBALS["CONFIG"]["GOOGLE_EMAIL_SENDER"],
        'clientSecret' => $GLOBALS["CONFIG"]["GOOGLE_CLIENT_SECRET"],
        'clientId' => $GLOBALS["CONFIG"]["GOOGLE_CLIENT_ID"],
        'refreshToken' => $GLOBALS["CONFIG"]["GOOGLE_REFRESH_TOKEN"],
    ]);
    return $oauth;
}

function gmail_send($subject, $body, $my_name, $recipient_email, $recipient_name, $isHTML=false) {
    global $logger;
    $mail = new PHPMailer();

    $mail->isSMTP();
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 465;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';

    $oauth = get_oauth();
    $mail->setOAuth($oauth);

    $mail->setFrom($GLOBALS["CONFIG"]["GOOGLE_EMAIL_SENDER"], $my_name);
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->Subject = $subject;
    $mail->CharSet = PHPMailer::CHARSET_UTF8;   // enables ä, ö

    $oauth = get_oauth();
    $mail->setOAuth($oauth);

    if ($isHTML)
        $mail->isHTML(true);
    $mail->Body = $body;

    // $mail->addAttachment('path/to/attachment.pdf');

    if ($mail->send()) {
        $logger->info("Email sent.", ["subject" => $subject, "to" => $recipient_email]);
    } else {
        $logger->error("Email send failed.", ["to" => $recipient_email, "msg" => $mail->ErrorInfo]);
    }
}

// gmail_send("Test subject", "Test body", "Webteam", $GLOBALS["CONFIG"]["GOOGLE_EMAIL_SENDER"], "Recipient");

?>