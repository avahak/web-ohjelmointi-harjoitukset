<?php

require_once "../logs/logger.php";
require_once "../config/pepper.php";
require_once "tokens.php";

$logger = new Logger();

// Checks that email, pw are found in the database (pw hashed).
// Returns id of the user on success and false otherwise.
function verify_password($conn, $email, $pw) {
    $pw_hash = null;
    $user_id = null;
    if ($email) {
        $stmt = "SELECT id, pw_hash FROM users WHERE email=?";
        $result = $conn->substitute_and_execute($stmt, $email);
        if ($result["success"]) 
            if ($row = $result["value"]->fetch_assoc())
                if (isset($row["pw_hash"])) {
                    $pw_hash = $row["pw_hash"];
                    $user_id = $row["id"];
                }
    }
    if ((!$pw_hash) || (!password_verify(PEPPER . $pw, $pw_hash)))
        return false;
    return $user_id;
}

// Returns user row that matches id with $user_id.
function user_data_from_id($conn, $user_id) {
    if (!$user_id)
        return null;
    $query = "SELECT * FROM users WHERE id=?";
    $result = $conn->substitute_and_execute($query, $user_id);
    if ((!$result['success']) || (mysqli_num_rows($result['value']) < 1))
        return null;
    return $result['value']->fetch_assoc();
}

// Returns user row that matches email with $email. (NOTE: email is unique)
function user_id_from_email($conn, $email) {
    if (!$email)
        return null;
    $query = "SELECT * FROM users WHERE email=?";
    $result = $conn->substitute_and_execute($query, $email);
    if ((!$result['success']) || (mysqli_num_rows($result['value']) < 1))
        return null;
    $row = $result['value']->fetch_assoc();
    return $row["id"];
}

// Adds a new user to the database.
function add_user($conn, $firstname, $lastname, $email, $phone, $pw) {
    $pw_hash = password_hash(PEPPER . $pw, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (firstname, lastname, email, phone, pw_hash) VALUES (?, ?, ?, ?, ?)";
    $result = $conn->substitute_and_execute($query, $firstname, $lastname, $email, $phone, $pw_hash);
    // should return user_id?
    return $result;
};

// Changes the status of the given user.
function change_user_status($conn, $user_id, $new_status) {
    $query = "UPDATE users SET status=? WHERE id=?";
    $result = $conn->substitute_and_execute($query, $new_status, $user_id);
    return $result;
}

// Returns user_id if user is logged in, null otherwise.
// If $redirect is set, redirects user to login if not logged.
function authenticate_user($conn, $redirect=false) {
    global $logger;
    if (isset($_SESSION["email"])) {
        // user has active session:
        return user_id_from_email($conn, $_SESSION["email"]);
    }
    if (isset($_COOKIE["remember_me"])) {
        // the cookie is set but cookies are client-side so gotta verify:
        $key = urldecode($_COOKIE["remember_me"]);
        $selector = substr($key, 0, 16);
        $validator = substr($key, 16);
        $user_id = verify_token($conn, $selector, $validator, "REMEMBER_ME", true);
        return $user_id;
    }

    if ($redirect) {
        // not logged in -> redirect user to login page:
        $target_page = htmlspecialchars($_SERVER["PHP_SELF"]);
        $_SESSION["target_page"] = $target_page;
        $logger->debug("Redirecting to login", ["target_page" => $target_page]);
        header("Location: login.php");
        exit();
    }

    return null;
}

// Creates a REMEMBER_ME token and sets a cookie "remember_me" with the token key.
function setup_remember_me($conn, $user_id) {
    global $logger;
    if (!$user_id)
        return null;

    // First check that user has no REMEMBER_ME cookies:
    $query = "SELECT * FROM tokens WHERE user_id=? AND token_type=?";
    $result = $conn->substitute_and_execute($query, $user_id, "REMEMBER_ME");
    if (($result['success']) && (mysqli_num_rows($result['value']) >= 1))
        return null;    // a token already exists

    $hours = 30 * 24;
    $token = create_token($conn, $user_id, "REMEMBER_ME", $hours);
    if (!$token)
        return null;

    $key = $token["selector"] . $token["validator"];
    $logger->debug("Setting up REMEMBER_ME token.", ["user_id" => $user_id, "key" => $key]);

    setcookie("remember_me", urlencode($key), time() + 3600*$hours);
    return true;
}

?>