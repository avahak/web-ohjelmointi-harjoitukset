<?php 

// On this page users can request new verification email.
// TODO

require_once "../../form_validation/validation/validation_php.php";
require_once "../../form_validation/validation/template_inputs.php";

require_once "shared_elements.php";
require_once "user_operations.php";
require_once "template_pages.php";
require_once "tokens.php";
require_once "mail/send_mail.php";

init();

function custom_validation() {
    // TODO check that email belongs to an UNVERIFIED user
    // and that user has less than ? EMAIL_VERIFICATION tokens
    $email = $_POST["email"] ?? "";
    $user_data = user_data_from_email($email);
    if ((!$user_data) || ($user_data["status"] != "UNVERIFIED")) {
        invalidate("email", "This email does not belong to an unverified account.");
        return;
    }
    // Check that user has at most x VERIFICATION_EMAIL tokens:
    $query = "SELECT * FROM tokens WHERE user_id=? AND token_type=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $user_data["id"], "EMAIL_VERIFICATION");
    echo "</br>NUM: " . mysqli_num_rows($result['value']);
    if (($result['success']) && (mysqli_num_rows($result['value']) > 2)) {
        // too many tokens of this type already (max 3)
        invalidate("email", "This account has too many requests for verification.");
        return;
    }
}

function validation_pass() {
    $email = $_POST["email"] ?? "";
    $user_data = user_data_from_email($email);
    $user_id = $user_data["id"];
    $fullname = $user_data["firstname"] . " " . $user_data["lastname"];

    $token = create_token($user_id, "EMAIL_VERIFICATION", 24);
    $key = urlencode($token["selector"] . $token["validator"]);

    $GLOBALS["g_logger"]->debug("Sending new verification email", compact("user_id", "key", "fullname", "email"));

    send_mail("Email verification link", email_template_verification_email_resend($key), 
            "Webteam", $email, $fullname, true);
    echo template_resend_verification_email_success($email);
    exit(); 
}

// Initialize the php script:
init_validation("./form_validation/e_form.json");
// Validate the form (this does nothing if there is no data in POST):
if (isset($_POST["resend_verification_email"]))
    validate("custom_validation", "validation_pass");
else
    echo "NO SUBMIT FROM THIS PAGE! (" . ($_POST["email"] ?? "") . ")";

shared_script_start("Resend Verification Email");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Resend Verification Email</h2>
                    <p>If you haven't gotten an email for account verification, please enter the email address associated with your account. We will send you another verification email.</p>
                    <form id="e_form" class="needs-validation" novalidate method="POST">
                        <input type="hidden" name="resend_verification_email" value="true">

                        <div class="mt-3">
                            <?php template_input("email", "email", "Email", "Email Placeholder", "col-12 col-sm-12", "col-12 col-sm-12"); ?>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Send Verification Email</button>
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