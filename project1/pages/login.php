<?php 

require_once "../../form_validation/validation/validation_php.php";
require_once "../../form_validation/validation/template_inputs.php";

require_once "shared_elements.php";
require_once "template_pages.php";
require_once "user_operations.php";
require_once "tokens.php";

init();

function custom_validation() {
    $email = $_POST["email"] ?? "";
    $pw = $_POST["pw"] ?? "";
    $user_id = verify_password($email, $pw);
    if (!$user_id) {
        // Password does not match:
        // invalidate("email", "Incorrect email or password.");
        invalidate("pw", "Incorrect email or password.");
        return;
    }
    $user_data = user_data_from_id($user_id);
    if ($user_data["status"] == "INACTIVE") {
        // User is flagged INACTIVE:
        invalidate("email", "This account is inactive. Contact support if needed.");
        return;
    }
    if ($user_data["status"] == "UNVERIFIED") {
        // User email not verified yet:
        // invalidate("email", "This account is has not been verified. Check your email. If you have not received your account verification email, click \"Account Recovery\" below and select \"Resend Email Verification\".");
        $s = <<<HTML
            <a href="resend_verification_email.php" onclick="submitToResendVerification(event);">here</a>
            HTML;
        invalidate("email", "This account is has not been verified. Check your email. If you have not received your account verification email, click $s.");
        return;
    }
}

function validation_pass() {
    $email = $_POST["email"] ?? "";
    $user_id = user_id_from_email($email);
    $GLOBALS["g_logger"]->debug("login.php POST", compact("user_id"));
    $_SESSION["user_id"] = $user_id;
    if (isset($_POST["remember_me"])) 
        setup_remember_me($user_id);
    $target_page = $_SESSION["target_page"] ?? "front.php";
    $_SESSION["target_page"] = null;
    header("Location: $target_page");
    exit();
}

// Initialize the php script:
init_validation("./form_validation/ep_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("custom_validation", "validation_pass");

shared_script_start("Login");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Login</h2>

                    <form id="ep_form" class="needs-validation" novalidate method="POST">

                        <div class="mt-4">
                            <?php template_input("email", "email", "Email", "Email Placeholder", "col-12 col-sm-12", "col-12 col-sm-12"); ?>
                        </div>
                        
                        <div class="mt-3" style="position:relative;">
                            <?php template_input("password", "pw", "Password", "Password Placeholder", "col-12 col-sm-12", "col-12 col-sm-12"); ?>
                            <div class="" style="position:absolute;top:0px;right:0px;">
                                <!-- <a href="about:blank">Forgot Password?</a> -->
                                <a href="request_reset_password.php" onclick="submitToRequestResetPassword(event);">Forgot Password?</a>
                            </div>
                        </div>

                        <div class="row mt-3 mb-5">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <input type="checkbox" class="form-check-input mx-1" id="remember_me" name="remember_me" value="true" <?= (recall('remember_me', false) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                        </div>

                    </form>

                    <?php template_account_recovery("ep_form"); ?>
                    
                    <a href="front.php">Back to Frontpage</a></br>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>