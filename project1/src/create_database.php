<?php

// Recreates the database so that we do not have to use phpmyadmin tool.
// WARNING! All existing info in the database is lost.

require_once __DIR__ . "/config.php";

require_once __DIR__ . "/../../sql_connect.php";
require_once __DIR__ . "/../../logs/logger.php";
recreate_database();
// Reason to not include these earlier is that they might assume that the database already exists.
require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/template_pages.php";
require_once __DIR__ . "/mail/mailtrap_send.php";

$logger = new Logger();

function recreate_database() {
    // Create a database-independent connection:
    $conn = new SqlConnection();
    $query = <<<SQL
        DROP DATABASE IF EXISTS web_admin_db; 
        CREATE DATABASE web_admin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
        SQL;
    $conn->multi_query($query);
    $conn->get_connection()->close();

    // Connect to the database, then load and execute SQL schema statements:
    $conn = new SqlConnection("web_admin_db");
    $db_schema = file_get_contents('web_admin_db.sql');
    echo nl2br(htmlspecialchars($db_schema));
    $conn->multi_query($db_schema);
    $conn->get_connection()->close();
}

// exit();

// add a few test users:
add_user("Admin", "", "admin@neilikka.fi", null, "");
add_user("Otto", "Mäkelä", "otto@otto.fi", "040-123456", "1234");
change_user_status(user_id_from_email("admin@neilikka.fi"), "ACTIVE");

// print_r(user_data_from_id($conn, user_id_from_email($conn, "wrong email")));
// print_r(user_data_from_id($conn, user_id_from_email($conn, "otto@otto.fi")));
// print_r(user_data_from_id($conn, user_id_from_email($conn, "admin@neilikka.fi")));

// create_token:
echo "</br>";
$user_id = user_id_from_email("otto@otto.fi");
$token1 = create_token($user_id, "EMAIL_VERIFICATION", 24);
$token2 = create_token($user_id, "REMEMBER_ME", 61*24);

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

$user_data = user_data_from_id($user_id);
$firstname = $user_data['firstname'];
$lastname = $user_data['lastname'];
$email = user_data_from_id($user_id)['email'];

echo "email: $email";

// mailtrap_send("Email verification link", email_template_verification_email($key), "Webteam", $email, $firstname . " " . $lastname, true);

?>