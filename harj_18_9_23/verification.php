<?php

// a simple page that verifies token given as url parameter
// used for EMAIL_VERIFICATION

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "tokens.php";
require_once "template_pages.php";

$conn = new SqlConnection("web_admin_db");
$logger = new Logger();

function verify($conn, $token_type) {
    global $logger;
    if (!isset($_GET["key"]))
        return false;
    $key = $_GET["key"];
    $selector = substr($key, 0, 16);
    $validator = substr($key, 16);
    $user_id = verify_token($conn, $selector, $validator, $token_type, true);

    $logger->debug("User verification in progress.", ["selector" => $selector, "validator" => $validator, "key" => $key, "user_id" => $user_id]);

    if ($user_id)
        change_user_status($conn, $user_id, "ACTIVE");
    return $user_id;
}

$user_id = verify($conn, "EMAIL_VERIFICATION");

echo template_email_verification_result($user_id);

?>
