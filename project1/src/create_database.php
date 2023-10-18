<?php

// Recreates the database so that we do not have to use phpmyadmin tool.
// WARNING! All existing info in the database is lost.

require_once __DIR__ . "/config.php";

require_once __DIR__ . "/../../sql_connect.php";
require_once __DIR__ . "/../../logs/logger.php";

$logger = new Logger();

// Wipes the existing database and creates a new one. 
// WARNING: All existing user data is deleted!
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
    $db_schema = file_get_contents(__DIR__ . '/./web_admin_db.sql');
    $conn->multi_query($db_schema);
    $conn->get_connection()->close();
}

// Crates an admin user with a random temporary password and sends that password to email.
function create_admin_user() {
    $pw = random_string(6);
    
    add_user("Admin", "admin@example.com", custom_password_hash($pw));
    change_user_status(user_id_from_email("admin@example.com"), "ACTIVE");
    change_user_role(user_id_from_email("admin@example.com"), "ADMIN");

    $body = "Temporary password for Admin is: " . $pw . "\nPlease change this when you log in.\n-TBA";
    send_mail("Temporary Admin Password", $body, "Webteam", 
        $GLOBALS["CONFIG"][$GLOBALS["CONFIG"]["EMAIL_SENDER"] . "_EMAIL_SENDER"], "Admin", false);
}

function create_random_users($num) {
    for ($k = 0; $k < $num; $k++) {
        $fake_user = new FakeUser();
        echo "<br>Should add: " . $fake_user->to_string();
        $name_rand = rand(0, 3);
        $name = $fake_user->firstname;
        if ($name_rand == 1) 
            $name = $fake_user->lastname;
        if ($name_rand == 2) 
            $name = $fake_user->firstname . " " . substr($fake_user->lastname, 0, 1);
        if ($name_rand == 3) 
            $name = $fake_user->firstname . " " . $fake_user->lastname;
        add_user($name, $fake_user->email, custom_password_hash($fake_user->password));
    }
}

recreate_database();
echo "<br>Created database.";

// Reason to not include these earlier is that they might assume that the database already exists.
require_once __DIR__ . "/fake_data.php";
require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/template_pages.php";
require_once __DIR__ . "/mail/send_mail.php";

create_admin_user();
echo "<br>Created admin.";

create_random_users(10);
echo "<br>Created random users.";

?>