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
function email_template_verification_email($key) {
    $site = $GLOBALS["CONFIG"]["SITE"];
    $content = <<<HTML
        <h1>Email Verification</h1>
        <p>Thank you for signing up on our website. To verify your email address, please click the link below:</p>
        <a href="{$site}/kurssi/project1/src/email_verification.php?key={$key}" class="btn btn-primary mb-3">Verify Email</a>
        <p>If you didn't sign up for our website, you can ignore this email.</p>
    HTML;
    return template_header("Email verification") . template_body($content);
}

// Template for the email that is RE-sent to the user with a link to verify their email.
function email_template_verification_email_resend($key) {
    $site = $GLOBALS["CONFIG"]["SITE"];
    $content = <<<HTML
        <h1>Email Verification</h1>
        <p>You have requested a new email verification link. To verify your email address, please click the link below:</p>
        <a href="{$site}/kurssi/project1/src/email_verification.php?key={$key}" class="btn btn-primary mb-3">Verify Email</a>
        <p>If you didn't sign up for our website or request a new verification email, you can ignore this message.</p>
    HTML;
    return template_header("Email verification") . template_body($content);
}

// Template to inform the user that signup was successful and a verification email was sent.
function template_signup_success($email) {
    $s_email = htmlspecialchars($email);
    $content = <<<HTML
        <h1>Signup successful!</h1>
        <p>Thank you for signing up on our website. A verification email has been sent to $s_email.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Signup successful") . template_body($content);
}

// Template to inform user that a new verification email has been sent.
function template_resend_verification_email_success($email) {
    $s_email = htmlspecialchars($email);
    $content = <<<HTML
        <h1>Verification email sent.</h1>
        <p>A verification email has been sent to $s_email.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Verification email") . template_body($content);
}

// Creates a simple page to inform the result of email verification
function template_email_verification_result($success) {
    echo template_header("Email verification");

    $content_success = <<<HTML
        <h1>Email Verification successful. </h1>
        <p>Your email has been successfully verified.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    $content_failure = <<<HTML
        <h1>Email verification failed. </h1>
        <p>Request new email verification on the login page.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    if ($success)
        echo template_body($content_success);
    else 
        echo template_body($content_failure);
}

// Creates a simple page to inform that password was changed
function template_change_password_result($success) {
    echo template_header("Change password");

    $content_success = <<<HTML
        <h1>Password change successful! </h1>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    $content_failure = <<<HTML
        <h1>Password change failed! </h1>
        <p>DEBUG! This should never happen. Investigate.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
        HTML;

    if ($success)
        echo template_body($content_success);
    else 
        echo template_body($content_failure);
}

// Template to inform the user that reset password email was sent.
function template_reset_password_email_sent($email) {
    $s_email = htmlspecialchars($email);
    $content = <<<HTML
        <h1>Password Reset</h1>
        <p>If the email address <a href="mailto:$email">$email</a> is registered, 
        you will receive a password reset email. Please check your inbox and spam 
        folder for instructions.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Password reset request") . template_body($content);
}

// Template email providing reset password link.
function email_template_reset_password($key) {
    $site = $GLOBALS["CONFIG"]["SITE"];
    $content = <<<HTML
        <h1>Reset password link</h1>
        <p>Click below to reset your password.</p>
        <a href="{$site}/kurssi/project1/src/reset_password.php?key={$key}" class="btn btn-primary mb-3">Reset password</a>
        <p>If you didn't request password reset, you can ignore this email.</p>
    HTML;
    return template_header("Reset password") . template_body($content);
}

// Informs the user that password reset link had invalid key.
function template_invalid_reset_password_link() {
    $content = <<<HTML
        <h1>Invalid password reset link</h1>
        <p>The password reset link was invalid or expired. Request another password reset email.</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Reset password") . template_body($content);
}

// Template for error situations that should not happen in normal conditions.
// Added this so that error handling logic for unexpected errors does not have 
// to be added to every page where such errors might rise.
function template_unexpected_error($msg) {
    $content = <<<HTML
        <h1>An unexpected error occured</h1>
        <p>$msg</p>
        <a href="front.php" class="btn btn-primary">Back to frontpage</a>
    HTML;
    return template_header("Unexpected error") . template_body($content);
}

// Adds account recovery button:
function template_account_recovery($form_name) {
    echo <<<HTML
        <script>
        // function to submit the form to request_reset_password.php:
        function submitToRequestResetPassword(event) {
            event.preventDefault();     // prevents following the link
            const form = document.getElementById("$form_name");
            form.action = "request_reset_password.php";
            form.submit();
        }
        // function to submit the form to resend_verification.php:
        function submitToResendVerification(event) {
            event.preventDefault();     // prevents following the link
            const form = document.getElementById("$form_name");
            form.action = "resend_verification_email.php";
            form.submit();
        }
        </script>

        <div class="my-3">
            <a class="btn btn-link m-0 p-0" role="button" data-bs-toggle="collapse" href="#accountRecovery" role="button" aria-expanded="false" aria-controls="accountRecovery">Account Recovery</a>
            <div class="collapse row mx-3" id="accountRecovery">
                <div>
                    <!-- Note: href="request_reset_password.php" does nothing here and is just here for the user: -->
                    <a href="request_reset_password.php" onclick="submitToRequestResetPassword(event);">Forgot my Password</a>
                </div>
                <div>
                    <a href="resend_verification.php" onclick="submitToResendVerification(event);">Resend Email Verification</a>
                </div>
            </div>
        </div>
        HTML;
}

?>