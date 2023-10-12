<?php

// token types: EMAIL_VERIFICATION, RESET_PASSWORD, REMEMBER_ME
// 
// User has (selector,validator), db has (id,user_id,token_type,selector,hashed_validator,expiry)
// selector and validator are random strings. selector is just for accessing 
// correct token database entry without giving away information on number of tokens.
// Token is validated if validator provided by user hashes into hashed_validator.
// Hashed_validator is found on the row that has selector matching the one provided by user.

require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/init.php";

init();

// creates a random string with $bytes bytes of information
function random_string($bytes) {
    // Alternative would be using bin2hex.
    // Assuming $bytes is divisible by 3, returns string with length: 4/3*$bytes.
    return base64_encode(random_bytes($bytes));
}

// Deletes all tokens that have expired.
function remove_expired_tokens() {
    $query = "DELETE FROM tokens WHERE expiry <= NOW()";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query);
}

// Deletes token with given selector.
function remove_token_with_selector($selector) {
    $query = "DELETE FROM tokens WHERE selector=?";
    $GLOBALS["g_conn"]->substitute_and_execute($query, $selector);
}

// Deletes all tokens of given type from the user, and all users tokens if no type is specified.
function remove_tokens($user_id, $token_type=null) {
    if (!$user_id)
        return;
    $GLOBALS["g_logger"]->debug("remove_tokens called.", compact("user_id", "token_type"));
    if ($token_type) {
        $stmt = "DELETE FROM tokens WHERE user_id=? AND token_type=?";
        $GLOBALS["g_conn"]->substitute_and_execute($stmt, $user_id, $token_type);
    } else {
        $stmt = "DELETE FROM tokens WHERE user_id=?";
        $GLOBALS["g_conn"]->substitute_and_execute($stmt, $user_id);
    }
}

// Creates a new token and returns all its info if successful, null otherwise.
function create_token($user_id, $token_type, $duration_hours) {
    // gotta do cleanup somewhere.. is this a good place?
    remove_expired_tokens();

    $stmt = "INSERT INTO tokens (user_id, token_type, selector, validator_hash, expiry) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))";
    $selector = random_string(12);      // 16 base64 chars
    $validator = random_string(48);     // 64 base64 chars
    $validator_hash = password_hash($validator, PASSWORD_DEFAULT);
    $result = $GLOBALS["g_conn"]->substitute_and_execute($stmt, $user_id, $token_type, $selector, $validator_hash, $duration_hours);
    if ($result["success"])
        return ["token_type" => $token_type, "selector" => $selector, "validator" => $validator];
    return null;
}

// Checks that the token is valid and returns user_id if so, otherwise false.
// If $consume_token=true, removes the token once verified.
function verify_token($selector, $validator, $token_type, $consume_token=false) {
    $stmt = "SELECT user_id, validator_hash FROM tokens WHERE selector=? AND token_type=? AND expiry > NOW()";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($stmt, $selector, $token_type);
    if ((!$result["success"]) || (mysqli_num_rows($result["value"]) != 1))
        return false;       // no tokens with given selector
    $row = $result["value"]->fetch_assoc();
    if (!password_verify($validator, $row['validator_hash'])) 
        return false;       // selector and validator do not match
    if ($consume_token)
        remove_token_with_selector($selector);
    return $row['user_id'];
}

// Creates a REMEMBER_ME token and sets a cookie "remember_me" with the token key.
function setup_remember_me($user_id) {
    if (!$user_id)
        return null;

    // First check that user has no REMEMBER_ME cookies:
    $query = "SELECT * FROM tokens WHERE user_id=? AND token_type=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $user_id, "REMEMBER_ME");
    if (($result['success']) && (mysqli_num_rows($result['value']) >= 1))
        return null;    // a token already exists

    $hours = 30 * 24;
    $token = create_token($user_id, "REMEMBER_ME", $hours);
    if (!$token)
        return null;

    $key = $token["selector"] . $token["validator"];
    $GLOBALS["g_logger"]->debug("Setting up REMEMBER_ME token.", ["user_id" => $user_id, "key" => $key]);

    setcookie("remember_me", urlencode($key), time() + 3600*$hours, "", "", false, true);
    return true;
}

// Creates a RESET_PASSWORD token for the user if possible
function create_reset_password_token($user_id) {
    if (!$user_id)
        return null;

    // First check that user has at most x RESET_PASSWORD tokens:
    $query = "SELECT * FROM tokens WHERE user_id=? AND token_type=?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $user_id, "RESET_PASSWORD");
    if (($result['success']) && (mysqli_num_rows($result['value']) > 2))
        return null;    // too many tokens of this type already (max 3)

    $hours = 24;
    $token = create_token($user_id, "RESET_PASSWORD", $hours);
    if (!$token)
        return null;

    $key = $token["selector"] . $token["validator"];
    $GLOBALS["g_logger"]->debug("Setting up RESET_PASSWORD token.", ["user_id" => $user_id, "key" => $key]);

    return $key;
}

?>