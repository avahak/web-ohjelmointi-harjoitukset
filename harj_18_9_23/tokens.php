<?php

// token types: EMAIL_VERIFICATION, RESET_PASSWORD, REMEMBER_ME
// 
// User has (selector,validator), db has (id,user_id,token_type,selector,hashed_validator,expiry)
// selector and validator are random strings. selector is just for accessing 
// correct token database entry without giving away information on number of tokens.
// Token is validated if validator provided by user hashes into hashed_validator.
// Hashed_validator is found on the row that has selector matching the one provided by user.

// TODO is token_type even needed?

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "db_operations.php";

$logger = new Logger();

// creates a random string with $bytes bytes of information
function random_string($bytes) {
    // Alternative would be using bin2hex.
    // Assuming $bytes is divisible by 3, returns string with length: 4/3*$bytes.
    return base64_encode(random_bytes($bytes));
}

// Deletes all tokens that have expired.
function remove_expired_tokens($conn) {
    $stmt = "DELETE FROM tokens WHERE expiry <= NOW()";
    $result = $conn->substitute_and_execute($stmt);
}

// Deletes the token that matches the selector.
function remove_token_with_selector($conn, $selector) {
    $stmt = "DELETE FROM tokens WHERE selector=?";
    $result = $conn->substitute_and_execute($stmt, $selector);
}

// Creates a new token and returns all its info if successful, null otherwise.
function create_token($conn, $user_id, $token_type, $duration_hours) {
    // gotta do cleanup somewhere.. is this a good place?
    remove_expired_tokens($conn);

    $stmt = "INSERT INTO tokens (user_id, token_type, selector, validator_hash, expiry) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))";
    $selector = random_string(12);      // 16 base64 chars
    $validator = random_string(48);     // 64 base64 chars
    $validator_hash = password_hash($validator, PASSWORD_DEFAULT);
    $result = $conn->substitute_and_execute($stmt, $user_id, $token_type, $selector, $validator_hash, $duration_hours);
    if ($result["success"])
        return ["token_type" => $token_type, "selector" => $selector, "validator" => $validator];
    return null;
}

// Checks that the token is valid and returns user_id if so, otherwise false.
// If $consume_token=true, removes the token once verified.
function verify_token($conn, $selector, $validator, $token_type, $consume_token=false) {
    $stmt = "SELECT user_id, validator_hash FROM tokens WHERE selector=? AND token_type=? AND expiry > NOW()";
    $result = $conn->substitute_and_execute($stmt, $selector, $token_type);
    if ((!$result["success"]) || (mysqli_num_rows($result["value"]) != 1))
        return false;       // no tokens with given selector
    $row = $result["value"]->fetch_assoc();
    if (!password_verify($validator, $row['validator_hash'])) 
        return false;       // selector and validator do not match
    if ($consume_token)
        remove_token_with_selector($conn, $selector);
    return $row['user_id'];
}

?>