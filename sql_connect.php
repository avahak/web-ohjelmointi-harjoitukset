<?php

// Usage: 
// 1) define $db = "database name here" 
// 2) require "(path to this file here)/sql_connect.php";
// 3) use $conn and optionally substitute_and_execute function for sql statements

require "config/sql_config.php";    // this just defines SERVER, USERNAME, PASSWORD

$conn = new mysqli(SERVER, USERNAME, PASSWORD, $db);

if ($conn->connect_error) {
    die("Connecting to database failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Use to sanitize strings that are inserted into html
function sanitize_for_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'utf-8');
}

// Function for safe parameter substitution into SQL statements using 
// prepared statements and placeholders. 
// Returns array with keys "success" and "value". Success is true iff no problems occured, 
// value is return value of statement on success or error message on failure.
//
// Example of use: 
// $result = prepare_statement($conn, "SELECT * FROM t1, t2 WHERE c1=? AND c2=?", param1, param2)
// if ($result['success']) { // success, operate with $result['value'] if it is needed }
// else { echo "ERROR: " . $result['value']; } 
function substitute_and_execute($conn, $stmt_text) {
    $params = array_slice(func_get_args(), 2);
    $n = count($params);
    try {
        $stmt = $conn->prepare($stmt_text);
        if (!$stmt)
            return array("success" => false, "value" => "Statement failed to prepare.");
        if ($n > 0) {
            $refs = array();
            foreach ($params as $key => $value) 
                $refs[$key] = &$params[$key];
            $s_params = array_merge(array(str_repeat('s', $n)), $refs);
            call_user_func_array(array($stmt, 'bind_param'), $s_params);
        }
        if (!$stmt->execute())
            return array("success" => false, "value" => "Statement failed to execute.");
        $result = $stmt->get_result();
        $stmt->close();
        return array("success" => true, "value" => $result);  // success
    } catch (mysqli_sql_exception $e) {
        return array("success" => false, "value" => $e->getMessage());
    } catch (ArgumentCountError $e) {
        return array("success" => false, "value" => $e->getMessage());
    }
}

?>