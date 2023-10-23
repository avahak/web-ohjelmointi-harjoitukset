<?php 

// Fields: name, email, pw, profile_picture
// Rejected fields: firstname, lastname, phone_number

require_once __DIR__ . "/../../form_validation/validation/validation_php.php";
require_once __DIR__ . "/../../form_validation/validation/template_inputs.php";
require_once __DIR__ . "/../../form_validation/validation/create_thumbnail.php";

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/init.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/template_pages.php";
require_once __DIR__ . "/mail/send_mail.php";

init();

function custom_validation() {
    $email = $_POST["email"] ?? "";
    if (user_id_from_email($email)) 
        invalidate("email", "Email is already used.");
}

function validation_pass() {
    $name = htmlspecialchars($_POST["name"] ?? "");
    $email = htmlspecialchars($_POST["email"] ?? "");
    $pw_hash = custom_password_hash($_POST["pw"] ?? "");
    $thumbnail_path = null;

    $image_tmp_file = $GLOBALS["form_validation_temporary_files"]["image"]["tmp_file"] ?? $_FILES["image"]["tmp_file"] ?? null;
    if ($image_tmp_file) {
        // Create a thumbnail:
        $thumbnail_path = "/project1/user_data/profile_pictures/" . random_string(10) . ".jpg";
        $result = create_thumbnail($image_tmp_file, __DIR__ . "/../../" . $thumbnail_path, $max_size=160);
        $GLOBALS["g_logger"]->info("Creating thumbnail", compact("thumbnail_path"));
        delete_file_if_exists($image_tmp_file);
    }

    $result = add_user($name, $email, $pw_hash, $thumbnail_path);
    if (!$result["success"]) {
        // Insert failed - this is an unexpected error
        echo template_unexpected_error("Adding new user failed.");
        exit(); 
    }

    // send verification email and tell user about it:
    $user_id = $GLOBALS["g_conn"]->get_connection()->insert_id;
    $token = create_token($user_id, "EMAIL_VERIFICATION", 24);
    $key = urlencode($token["selector"] . $token["validator"]);

    $GLOBALS["g_logger"]->debug("Adding new user", compact("user_id", "key", "name", "email"));

    send_mail("Email verification link", email_template_verification_email($key), 
            "Webteam", $email, $name, true);
    echo template_signup_success($email);
    exit(); 
}

// Initialize the php script:
init_validation("./form_validation/signup_form.json", __DIR__ . "/../../my_temporary_files/");
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
                    <form id="signup_form" class="needs-validation" enctype="multipart/form-data" novalidate method="POST">

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <?php template_input("text", "name", "Name", "Name", "col-12", "col-12"); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php template_input("email", "email", "Email", "Enter email address", "col-12", "col-12"); ?>
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

                        <div class="mt-3">
                            <?php template_file_upload("image", "Upload Profile Picture"); ?>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-6 order-2 order-sm-1">
                                <button type="submit" class="btn btn-primary mt-4">Submit</button>
                                <?php template_account_recovery("signup_form"); ?>
                                <a href="front.php">Back to Frontpage</a>
                            </div>
                            <div class="col-sm-6 order-1 order-sm-2">
                                <?php template_file_upload_image_preview("image", "Profile Picture Preview", 150); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>