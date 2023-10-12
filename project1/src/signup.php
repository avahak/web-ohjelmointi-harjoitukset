<?php 

require_once "../../form_validation/validation/validation_php.php";
require_once "../../form_validation/validation/template_inputs.php";

require_once "shared_elements.php";
require_once "init.php";
require_once "user_operations.php";
require_once "tokens.php";
require_once "template_pages.php";
require_once "mail/send_mail.php";

init();

function custom_validation() {
    $email = $_POST["email"] ?? "";
    if (user_id_from_email($email)) 
        invalidate("email", "Email is already used.");
}

function validation_pass() {
    $firstname = $_POST["firstname"] ?? "";
    $lastname = $_POST["lastname"] ?? "";
    $email = $_POST["email"] ?? "";
    $phone = $_POST["phone"] ?? "";
    $pw = $_POST["pw"] ?? "";
    $result = add_user($firstname, $lastname, $email, $phone, $pw);
    if (!$result["success"]) {
        // Insert failed - this is an unexpected error
        echo template_unexpected_error("Adding new user failed.");
        exit(); 
    }

    // send verification email and tell user about it:
    $user_id = $GLOBALS["g_conn"]->get_connection()->insert_id;
    $fullname = $firstname . " " . $lastname;
    $token = create_token($user_id, "EMAIL_VERIFICATION", 24);
    $key = urlencode($token["selector"] . $token["validator"]);

    $GLOBALS["g_logger"]->debug("Adding new user", ["user_id" => $user_id, "key" => $key, "fullname" => $fullname, "email" => $email]);

    send_mail("Email verification link", email_template_verification_email($key), 
            "Webteam", $email, $fullname, true);
    echo template_signup_success($email);
    exit(); 
}

// Initialize the php script:
init_validation("./form_validation/signup_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("custom_validation", "validation_pass");

shared_script_start("Signup");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <?php create_alert(); ?>
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Signup</h2>
                    <form id="signup_form" class="needs-validation" novalidate method="POST">

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <?php template_input("text", "firstname", "First Name", "First Name", "col-12", "col-12"); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php template_input("text", "lastname", "Last Name", "Last Name", "col-12", "col-12"); ?>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <?php template_input("email", "email", "Email", "Enter email address", "col-12", "col-12"); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php template_input("text", "phone", "Phone Number", "Phone Number", "col-12", "col-12"); ?>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <?php template_input("password", "pw", "Password", "Enter password", "col-12", "col-12"); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php template_input("password", "pw2", "Password (Confirmation)", "Retype password", "col-12", "col-12"); ?>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary mt-4">Submit</button>
                    </form>

                    <div class="mt-3">
                        <?php template_account_recovery("signup_form"); ?>
                        <a href="front.php">Back to Frontpage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>