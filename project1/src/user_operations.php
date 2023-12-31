<?php

require_once __DIR__ . "/tokens.php";
require_once __DIR__ . "/init.php";

init();

// Checks that email, pw are found in the database (pw hashed).
// Returns id of the user on success and false otherwise.
function verify_password($email, $pw) {
    $pw_hash = null;
    $user_id = null;
    if ($email) {
        $stmt = "SELECT id, pw_hash FROM users WHERE email=?";
        $result = $GLOBALS["g_conn"]->substitute_and_execute($stmt, $email);
        if ($result["success"]) 
            if ($row = $result["value"]->fetch_assoc())
                if (isset($row["pw_hash"])) {
                    $pw_hash = $row["pw_hash"];
                    $user_id = $row["id"];
                }
    }
    if ((!$pw_hash) || (!password_verify($GLOBALS["CONFIG"]["PEPPER"] . $pw, $pw_hash)))
        return false;
    return $user_id;
}

// Hashing function for passwords. Implemented to use the method every time.
function custom_password_hash($pw) {
    return password_hash($GLOBALS["CONFIG"]["PEPPER"] . $pw, PASSWORD_DEFAULT);
}

// Changes the user password 
function change_password($user_id, $new_pw) {
    $new_pw_hash = custom_password_hash($new_pw);
    $GLOBALS["g_logger"]->warning("change_password called", compact("user_id", "new_pw_hash"));
    $query = "UPDATE users SET pw_hash=? WHERE id=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $new_pw_hash, $user_id);
    return $result["success"];
}

// Returns user row that matches id with $user_id.
function user_data_from_id($user_id) {
    if (!$user_id)
        return null;
    $query = "SELECT * FROM users WHERE id=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $user_id);
    if ((!$result['success']) || (mysqli_num_rows($result['value']) < 1))
        return null;
    return $result['value']->fetch_assoc();
}

// Returns user row that matches email with $email. (NOTE: email is unique)
function user_id_from_email($email) {
    if (!$email)
        return null;
    $query = "SELECT * FROM users WHERE email=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $email);
    if ((!$result['success']) || (mysqli_num_rows($result['value']) < 1))
        return null;
    $row = $result['value']->fetch_assoc();
    return $row["id"];
}

// Convenience function combining user_data_from_id and user_id_from_email.
function user_data_from_email($email) {
    $user_id = user_id_from_email($email);
    return ($user_id ? user_data_from_id($user_id) : null);
}

// Adds a new user to the database.
function add_user($name, $email, $pw_hash, $profile_picture_path=null) {
    $query = "INSERT INTO users (name, email, pw_hash, profile_picture_path) VALUES (?, ?, ?, ?)";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $name, $email, $pw_hash, $profile_picture_path);
    // should return user_id?
    return $result;
};

// Changes the status of the given user.
function change_user_status($user_id, $new_status) {
    $query = "UPDATE users SET status=? WHERE id=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $new_status, $user_id);
    return $result;
}

// Changes the role of the given user.
function change_user_role($user_id, $new_role) {
    $query = "UPDATE users SET role=? WHERE id=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $new_role, $user_id);
    return $result;
}

// Returns user_id if user is logged in, null otherwise.
// If $redirect is set, redirects user to login if not logged.
function authenticate_user($redirect=false) {
    if (isset($_SESSION["user_id"])) {
        // user has active session:
        return $_SESSION["user_id"];
    }
    if (isset($_COOKIE["remember_me"])) {
        // the cookie is set but cookies are client-side so gotta verify:
        $key = urldecode($_COOKIE["remember_me"]);
        $selector = substr($key, 0, 16);
        $validator = substr($key, 16);
        $user_id = verify_token($selector, $validator, "REMEMBER_ME", false);
        if ($user_id) {
            $_SESSION["user_id"] = $user_id;
            return $user_id;
        }
        // remember_me cookie verification failed - remove the cookie on next init:
        $_SESSION["INVALIDATE_REMEMBER_ME"] = true;
    }

    if ($redirect) {
        // not logged in -> redirect user to login page:
        $target_page = htmlspecialchars($_SERVER["SCRIPT_NAME"]);
        $_SESSION["target_page"] = $target_page;
        $GLOBALS["g_logger"]->debug("Redirecting to login", ["target_page" => $target_page]);
        header("Location: login.php");
        exit();
    }

    return null;
}

// Logs the user out:
function logout() {
    $user_id = authenticate_user(false);
    // Using session_unset() takes immediate effect, unlike session_destroy() alone
    session_unset();
    if ($user_id)
        remove_tokens($user_id, "REMEMBER_ME");
    $_SESSION["INVALIDATE_REMEMBER_ME"] = true;
}

?>