<?php 

// This page is used for entering a new password after a password 
// reset request has been submitted. 
// Use change_password.php for changing user password while logged in.

require_once "../../form_validation/validation/validation_php.php";
require_once "../../form_validation/validation/template_inputs.php";

require_once "shared_elements.php";
require_once "init.php";
require_once "user_operations.php";
require_once "template_pages.php";

init();

function verify_reset_password_url_key() {
    if (!isset($_GET["key"]))
        return false;
    $key = $_GET["key"];
    $selector = substr($key, 0, 16);
    $validator = substr($key, 16);
//TODO CHANGE TO true once enough testing is done!
    $user_id = verify_token($selector, $validator, "RESET_PASSWORD", false);
    $GLOBALS["g_logger"]->debug("Password reset in progress.", ["selector" => $selector, "validator" => $validator, "key" => $key, "user_id" => $user_id]);
    return $user_id;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // here we get the key and from it the user:
    $user_id = verify_reset_password_url_key();
    if (!$user_id) {
        echo template_invalid_reset_password_link();
        exit();
    }
    // We need to pass user_id from here at GET to later at POST:
    $_SESSION["reset_password_user_id"] = $user_id;
    echo "</br>GET: TOKEN WAS VALID - user_id: $user_id";
}

function validation_pass() {
    // read and unset the session variable:
    $user_id = $_SESSION["reset_password_user_id"];
    unset($_SESSION["reset_password_user_id"]);

    $pw = $_POST["pw"] ?? "";
    $GLOBALS["g_logger"]->warning("reset_password POST changing pw", compact("user_id", "pw"));
    change_password($user_id, $pw);
    echo template_change_password_result(true);
    exit();
}

// Initialize the php script:
init_validation("./form_validation/pp_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate(null, "validation_pass");

shared_script_start("Reset Password");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:500px;">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Reset password</h2>

                    <form id="pp_form" class="needs-validation" novalidate method="POST" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">

                        <p>Enter the new password twice to reset your password.</p>

                        <div class="col mt-3">
                            <?php template_input("password", "pw", "New Password", "Enter New Placeholder", "col-12", "col-12"); ?>
                        </div>

                        <div class="col mt-3">
                            <?php template_input("password", "pw2", "New Password (Confirmation)", "Re-enter New Password", "col-12", "col-12"); ?>
                        </div>
                    
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                    <div>
                        <a href="front.php">Back to frontpage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>