<?php

// a simple page that verifies token given as url parameter
// used for EMAIL_VERIFICATION

require_once "init.php";
require_once "tokens.php";
require_once "template_pages.php";

init();

function verify_email_verification_url_key() {
    if (!isset($_GET["key"]))
        return false;
    $key = $_GET["key"];
    $selector = substr($key, 0, 16);
    $validator = substr($key, 16);
    $user_id = verify_token($selector, $validator, "EMAIL_VERIFICATION", true);

    $GLOBALS["g_logger"]->debug("User verification in progress.", compact("selector", "validator", "key", "user_id"));

    if ($user_id)
        change_user_status($user_id, "ACTIVE");
    return $user_id;
}

$user_id = verify_email_verification_url_key();

echo template_email_verification_result($user_id);

?>
