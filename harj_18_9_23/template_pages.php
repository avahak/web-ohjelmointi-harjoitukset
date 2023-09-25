<?php

define("BOOTSTRAP", <<<HTML
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    HTML);

// template for a simple header
function template_header($title) {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    HTML . BOOTSTRAP . <<<HTML
        <title>$title</title>
        <style>
            body, html {
                margin: 0;
                padding: 0;
            }
            body {
                font-family: Arial, sans-serif;
                text-align: center;
            }
            .container {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
        </style>
    </head>
    HTML;
}

// template for a simple body with given content
function template_body($content) {
    return <<<HTML
    <body class="bg-dark text-light">
        <div class="container">
            $content
        </div>
    </body>
    </html>
    HTML;
}

// Template for the email that is sent to the user with a link to verify their email.
function template_email_verification_email($key) {
    $content = <<<HTML
        <h1>Email Verification</h1>
        <p>Thank you for signing up on our website. To verify your email address, please click the link below:</p>
        <a href="http://127.0.0.1/kurssi/harj_18_9_23/verification.php?key={$key}" class="btn btn-primary mb-3">Verify Email</a>
        <p>If you didn't sign up for our website, you can ignore this email.</p>
    HTML;
    return template_header("Email verification ") . template_body($content);
}

// Template to inform the user that signup was successful and a verification email was sent.
function template_signup_success($email) {
    $s_email = htmlspecialchars($email);
    $content = <<<HTML
        <h1>Signup successful!</h1>
        <p>Thank you for signing up on our website. A verification email has been sent to $s_email.</p>
        <a href="base.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Signup successful") . template_body($content);
}

function template_email_verification_result($success) {
    echo template_header("Email verification");

    $content_success = <<<HTML
        <h1>Email Verification successful. </h1>
        <p>Your email has been successfully verified.</p>
        <a href="base.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    $content_failure = <<<HTML
        <h1>Email verification failed. </h1>
        <p>Request new email verification on the log in page.</p>
        <a href="base.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    if ($success)
        echo template_body($content_success);
    else 
        echo template_body($content_failure);
}

?>