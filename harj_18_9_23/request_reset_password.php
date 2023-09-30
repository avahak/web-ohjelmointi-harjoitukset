<?php 

// On this page users can enter their password and request a reset password email.

require_once "user_operations.php";
require_once "template_pages.php";
require_once "tokens.php";
require_once "send_mail.php";

init();

// Returns token key if a RESET_PASSWORD token was created, otherwise null.
function create_reset_token($email) {
    $user_id = user_id_from_email($email);
    if ($user_id)
        return urlencode(create_reset_password_token($user_id));
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["reset_password"])) {
        // Email address provided passes client-side verification so 
        // whatever happens here, we should tell the user that an email has been sent.

        $email = $_POST["email"];
        echo "SUBMIT! " . $email;

        $GLOBALS["g_logger"]->debug("Password reset requested.", compact("email"));

        $key = create_reset_token($email);

        if ($key) {
            send_mail("Reset password link", email_template_reset_password($key), 
                "Webteam", $email, "Account holder", true);
        } else {
            // sleep(1);   // TODO
            echo "DEBUG: Faking sending email to (" . $_POST["email"] . ")";
        }
        echo template_reset_password_email_sent($email);
        exit(); 
    } else {
        echo "NO SUBMIT FROM THIS PAGE! (" . $_POST["email"] . ")";
    }
}

function recall_email() {
    return htmlspecialchars($_POST["email"] ?? "");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="col-md-8 col-lg-6 container mt-5 text-dark">
        <div class="row justify-content-center">
            <div class="col">
                <form name="form" class="needs-validation <?php echo ((($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST["forgot_password"]))) ? "was-validated" : ""); ?>" novalidate method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="reset_password" value="true">
                    <div class="h3 mb-3">Reset your password</div>
                    <p>Forgot your password? Please enter the email address associated with your account. We will send you a link to reset your password.</p>
                    <div class="form-group col-lg-8">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="email" id="email" <?php echo "value=\"" . recall_email() . "\"" ?> required>
                        <div class="invalid-feedback" id="email-feedback">
                            Enter a valid email address.
                        </div>
                    </div>

                    <div class="row my-3">
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Send reset link</button>
                            <p class="mt-3"><a href="front.php">Back to frontpage</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>