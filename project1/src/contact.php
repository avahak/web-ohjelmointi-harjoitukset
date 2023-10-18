<?php 

// TODO add $s_name, $s_email as default values to name, email fields.

require_once __DIR__ . "/../../form_validation/validation/validation_php.php";
require_once __DIR__ . "/../../form_validation/validation/template_inputs.php";

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/init.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/template_pages.php";
require_once __DIR__ . "/mail/send_mail.php";

init();
$user_id = authenticate_user(false);
if ($user_id) {
    $user_data = user_data_from_id($user_id);
    $s_email = htmlspecialchars($user_data["email"]);
    $s_name = htmlspecialchars($user_data["name"]);
}

function custom_validation() {
}

function validation_pass() {
    $s_name = htmlspecialchars($_POST["name"] ?? "");
    $s_email = htmlspecialchars($_POST["email"] ?? "");
    $s_msg = htmlspecialchars($_POST["message"] ?? "");
    $body = "Name: $s_name\nEmail: $s_email\nMessage: $s_msg";
    $website_email = $GLOBALS["CONFIG"][$GLOBALS["CONFIG"]["EMAIL_SENDER"] . "_EMAIL_SENDER"];
    send_mail("Contact Us Feedback", $body, "TBA contact.php", $website_email, "TBA Website", false);
    echo template_contact_us_success();
    exit(); 
}

// Initialize the php script:
init_validation("./form_validation/contact_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("custom_validation", "validation_pass");

shared_script_start("Contact Us");

// The following includes javascript validation code:
include_validation_js("../../form_validation/validation/");
?>

<div class="container mb-3">
    <div class="row d-flex justify-content-center mt-4">
        <div class="col" style="max-width:600px;">
            <?php create_alert(); ?>
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <h2 class="card-title text-center">Contact Us</h2>
                    <p>If you prefer, you can reach us via email at 
                        <span class="text-muted" style="font-family: monospace; font-weight: bold;">example[at]<span class="d-none">obfuscation text for robots</span>gmail[dot]com</span>.
                        Otherwise, use the contact form below:
                    </p>
                    <form id="contact_form" class="needs-validation" novalidate method="POST">

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <?php template_input("text", "name", "Name", "Name", "col-12", "col-12"); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php template_input("email", "email", "Email", "Email", "col-12", "col-12"); ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <?php template_textarea("message", "Enter your message to use:", "Message", 3); ?>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary mt-4">Submit</button>
                    </form>

                    <div class="mt-3">
                        <a href="front.php">Back to Frontpage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php shared_script_end(); ?>