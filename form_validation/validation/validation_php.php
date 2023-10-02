<?php

// This script contains php portion of form validation based on JSON rules
// and some helper functions for template_inputs.php.

// ON THE STRUCTURE OF THE JSON FILE:
// VALIDATION_DEFAULT_MESSAGES are default messages used when no specific message 
// is defined in VALIDATION_MESSAGES:
// VALIDATION_RULES define the restrictions placed on the input fields
// VALIDATION_MESSAGES are custom messages for validation failure communication. 
// %1 refers to field name text, %2 to value of the rule
// VALIDATION_TRIGGERS is used by javascript to validate other fields in addition
// to the one changed (used with force_equality).

// Reads the validation json file.
function init_validation($file) {
    // list of input field names that fail server side validation:
    $GLOBALS["invalidate_errors"] = [];

    $GLOBALS["VALIDATION_JSON_STRING"] = file_get_contents($file);
    $GLOBALS["VALIDATION_JSON_DECODE"] = json_decode($GLOBALS["VALIDATION_JSON_STRING"], true);
}

// Performs server-side validation. First does validation based on the rules in
// the JSON file. If no errors were found, performs custom validation by calling 
// $custom_validation. If custom validation does not add any errors by calling invalidate, 
// then form passes all validation and $validation_pass is called.
function validate(callable $custom_validation, callable $validation_pass) {
    // Server-side validation goes here:
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check validation rules defined in the JSON file:
        json_validate($_POST);

        if (!$GLOBALS["invalidate_errors"]) {
            // Form passes JSON validation rules, perform custom validation:
            $custom_validation();
        }

        if (!$GLOBALS["invalidate_errors"]) {
            // Form passes all validation:
            $validation_pass();
        }
    }
}

// Marks field with name $name as invalid with given error message.
function invalidate($name, $message) {
    $GLOBALS["invalidate_errors"][$name] = $message;
}

// Validates all fields whose name is in $arr.
function json_validate($arr) {
    $GLOBALS["invalidate_errors"] = [];

    foreach ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"] as $name => $rule) {
        $value = $arr[$name] ?? "";
        foreach ($rule as $rule_name => $rule_value) {
            $is_valid = true;

            if (($rule_name == "required") && ($rule_value))
                if ($value === "")
                    $is_valid = false;

            if ($rule_name == "min_length")
                if (strlen($value) < $rule_value)
                    $is_valid = false;

            if ($rule_name == "max_length")
                if (strlen($value) > $rule_value)
                    $is_valid = false;

            if (strpos($rule_name, "pattern") === 0)     // $rule_name starts with "pattern"
                if (!preg_match("/" . $rule_value . "/", $value))
                    $is_valid = false;

            if (($rule_name == "numeric") && ($rule_value))
                if (!is_numeric($value))
                    $is_valid = false;

            if ($rule_name == "min")
                if ($value < $rule_value)
                    $is_valid = false;
        
            if ($rule_name == "max")
                if ($value > $rule_value)
                    $is_valid = false;

            if ($rule_name == "force_equality")
                if (isset($arr[$rule_value]))
                    if ($arr[$rule_value] != $value)
                        $is_valid = false;

            if ($rule_name == "min_selected")
                if (count($value) < $rule_value)
                    $is_valid = false;

            if ($rule_name == "max_selected")
                if (count($value) > $rule_value)
                    $is_valid = false;

            if (!$is_valid) {
                $default_msg = (strpos($rule_name, "pattern") === 0) 
                    ? ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_DEFAULT_MESSAGES"]["pattern"] ?? "") 
                    : ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_DEFAULT_MESSAGES"][$rule_name] ?? "");
                $msg = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_MESSAGES"][$name][$rule_name] ?? $default_msg;
                $name_text = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_MESSAGES"][$name]["text"] ?? $name;
                $msg = str_replace("%1", $name_text, $msg);
                $msg = str_replace("%2", $rule_value, $msg);
                invalidate($name, ucfirst($msg));
                break;
            }
        }
    }
}

// Used to add class for bootstrap to tell which inputs failed server-side validation.
function validation_class($name) {
    if (empty($GLOBALS["invalidate_errors"]))
        return "";          // form is fresh - do nothing
    if (array_key_exists($name, $GLOBALS["invalidate_errors"]))
        return "is-invalid";    // an error message exists for the field - invalid
    if (!array_key_exists($name, $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"]))
        return "";      // no rules for field - do not attempt to validate
    if (recall($name, false) != ($_POST[$name] ?? ""))
        return "is-invalid";    // recall prevented and input nonempty - invalid  
    return "is-valid";  // default to valid
}

// Used to display server-side validation errors
function custom_feedback($name) {
    if (array_key_exists($name, $GLOBALS["invalidate_errors"]))
        return $GLOBALS["invalidate_errors"][$name];
    return "";
}

// Form data retention helper function:
function recall($name, $sanitize) {
    $prevent_recall = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"][$name]["prevent_recall"] ?? false;
    if ($prevent_recall)
        return "";
    $value = $_POST[$name] ?? "";
    return ($sanitize ? htmlspecialchars($value) : $value);
}

// Returns true if field contained some information.
function user_modified($name) {
    $value = $_POST[$name] ?? "";
    return ($value ? "user-modified" : "");
}

// Adds required javascript (found in $relative_path) to the page.
// This goes in the header of the form php. Relative path is path from
// form script to this script.
function include_validation_js($relative_path="./") {
    echo "<script>const VALIDATION_JSON = " . $GLOBALS["VALIDATION_JSON_STRING"] . ";</script>";
    echo "<script src=\"{$relative_path}validation_js.js\"></script>";
}

// Creates an alert that contains server-side error messages:
function create_alert($debug=false) {
    // Do not create an alert for a fresh form:
    if ($_SERVER["REQUEST_METHOD"] != "POST")
        return;

    if ($GLOBALS["invalidate_errors"]) {
        // found a flaw - report the found flaws to the user
        echo <<<HTML
            <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="h5">ERROR:</div>
            HTML;
        foreach ($GLOBALS["invalidate_errors"] as $name => $value) 
            echo "<li>$value</li>";
        echo <<<HTML
            <button class="btn-close" aria-label="close" data-bs-dismiss="alert">
            </button></div>
            HTML;
    }
    if ($debug) {  
        // make an alert that displays the input values:
        echo <<<HTML
            <div class="alert alert-primary alert-dismissible" role="alert">
            <div class="h5">[DEBUG] Form input:</div>
            HTML;
        foreach ($_POST as $key => $value) {
            if (is_array($value))
                echo htmlspecialchars($key) . ": " . htmlspecialchars(var_export($value, true)) . "<br>";
            else
                echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
        }
        echo <<<HTML
            <button class="btn-close" aria-label="close" data-bs-dismiss="alert">
            </button></div>
            HTML;
    }
}

?>