<?php 

// This page is used by logged in users to change passwords.
// Use reset_password.php for entering new password after password reset instead.

require_once "../../form_validation/validation/validation_php.php";
require_once "../../form_validation/validation/template_inputs.php";

require_once "shared_elements.php";
require_once "init.php";
require_once "user_operations.php";
require_once "template_pages.php";

$user_id = init_secure_page(true);

function custom_validation() {
    $user_data = user_data_from_id($GLOBALS["user_id"]);
    if (!verify_password($user_data["email"], $_POST["pw"])) {
        echo "POST - INCORRECT pw - invalidate";
        invalidate("pw", "Incorrect password.");
    }
}

function validation_pass() {
    $result = change_password($GLOBALS["user_id"], $_POST["pw2"]);
    echo template_change_password_result($result ? true : false);
    exit();
}

// Initialize the php script:
init_validation("./form_validation/ppp_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("custom_validation", "validation_pass");

shared_script_start("Change Password");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Change Password</h2>
                    <form id="ppp_form" class="needs-validation <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" ? "was-validated" : ""); ?>" novalidate method="POST">

                        <div class="col mt-3">
                            <?php template_input("password", "pw", "Current Password", "Current Placeholder", "col-12", "col-12"); ?>
                        </div>

                        <div class="col mt-3">
                            <?php template_input("password", "pw2", "New Password", "Enter New Password", "col-12", "col-12"); ?>
                        </div>
                    
                        <div class="col mt-3">
                            <?php template_input("password", "pw3", "New Password (Confirmation)", "Re-enter New Password", "col-12", "col-12"); ?>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                    <a href="front.php">Back to Frontpage</a></br>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>