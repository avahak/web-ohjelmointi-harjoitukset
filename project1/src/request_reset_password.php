<?php 

// On this page users can enter their email and request a reset password email.

require_once __DIR__ . "/../../form_validation/validation/validation_php.php";
require_once __DIR__ . "/../../form_validation/validation/template_inputs.php";

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/template_pages.php";
require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/mail/send_mail.php";

init();

// Returns token key if a RESET_PASSWORD token was created, otherwise null.
function create_reset_token($email) {
    $user_id = user_id_from_email($email);
    if ($user_id)
        return urlencode(create_reset_password_token($user_id));
    return null;
}

function validation_pass() {
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
}

// Initialize the php script:
init_validation("./form_validation/e_form.json");
// Validate the form (this does nothing if there is no data in POST):
if (isset($_POST["reset_password"]))
    validate(null, "validation_pass");
else
    echo "NO SUBMIT FROM THIS PAGE! (" . ($_POST["email"] ?? "") . ")";

shared_script_start("Request Reset Password");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Reset Password</h2>
                    <p>Forgot your password? Please enter the email address associated with your account. We will send you a link to reset your password.</p>
                    <form id="e_form" class="needs-validation" novalidate method="POST">
                        <input type="hidden" name="reset_password" value="true">

                        <div class="mt-3">
                            <?php template_input("email", "email", "Email", "Email Placeholder", "col-12 col-sm-12", "col-12 col-sm-12"); ?>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Send Reset Link</button>
                                <p class="mt-3"><a href="front.php">Back to Frontpage</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>