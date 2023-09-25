<?php

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "tokens.php";
require_once "db_operations.php";
require_once "template_pages.php";
require_once "send_mail.php";

$conn = new SqlConnection("web_admin_db");
$logger = new Logger();

// load and execute SQL schema statements:
$db_schema = file_get_contents('web_admin_db.sql');
echo nl2br(htmlspecialchars($db_schema));
$conn->multi_query($db_schema);

// add a few test users:
add_user($conn, "Admin", "", "admin@neilikka.fi", null, "");
add_user($conn, "Otto", "Mäkelä", "otto@otto.fi", "040-123456", "1234");
change_user_status($conn, user_id_from_email($conn, "admin@neilikka.fi"), "ACTIVE");

// print_r(user_data_from_id($conn, user_id_from_email($conn, "wrong email")));
// print_r(user_data_from_id($conn, user_id_from_email($conn, "otto@otto.fi")));
// print_r(user_data_from_id($conn, user_id_from_email($conn, "admin@neilikka.fi")));

// create_token:
echo "</br>";
$user_id = user_id_from_email($conn, "otto@otto.fi");
$token1 = create_token($conn, $user_id, "EMAIL_VERIFICATION", 24);
$token2 = create_token($conn, $user_id, "REMEMBER_ME", 61*24);

$logger->debug("Created new token.", ["token1" => var_export($token1, true)]);

// verify_token:
$selector = $token1["selector"];
$validator = $token1["validator"];
echo "Selector: " . $selector . "</br>";
echo "Validator: " . $validator . "</br>";

// $key = $selector . $validator;
// echo "key: " . $key . "</br>";
// $key = urlencode($key);
// echo "key: " . $key . "</br>";
// $key = urldecode($key);
// echo "key: " . $key . "</br>";

$key = urlencode($selector . $validator);

$user_data = user_data_from_id($conn, $user_id);
$firstname = $user_data['firstname'];
$lastname = $user_data['lastname'];
$email = user_data_from_id($conn, $user_id)['email'];

send_mail("Email verification link", template_email_verification_email($key), "Webteam", $email, $firstname . " " . $lastname, true);

?>