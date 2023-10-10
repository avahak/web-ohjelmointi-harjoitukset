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
function init_validation($json_file, $temporary_upload_directory="./") {
    if(!session_id())
        session_start();
    if ($_SERVER["REQUEST_METHOD"] != "POST")   // form is fresh
        $_SESSION["form_validation_temporary_files"] = [];
    if (!isset($_SESSION["form_validation_temporary_files"]))   // just in case
        $_SESSION["form_validation_temporary_files"] = [];
    // List of feedback for invalid fields:
    $GLOBALS["validation_invalid_feedback"] = [];

    $GLOBALS["temporary_upload_directory"] = $temporary_upload_directory;

    $GLOBALS["VALIDATION_JSON_STRING"] = file_get_contents($json_file);
    $GLOBALS["VALIDATION_JSON_DECODE"] = json_decode($GLOBALS["VALIDATION_JSON_STRING"], true);
}

// Performs server-side validation. First does validation based on the rules in
// the JSON file. If no errors were found, performs custom validation by calling 
// $custom_validation. If custom validation does not add any errors by calling invalidate, 
// then form passes all validation and $validation_pass is called.
// NOTE: PHP allows you to provide default values of null for parameters with type 
//       declarations, even if the type declaration would normally prevent null values.
function validate(callable $custom_validation=null, callable $validation_pass=null) {
    // Server-side validation goes here:
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check validation rules defined in the JSON file:
        json_validate();

        if (!$GLOBALS["validation_invalid_feedback"]) {
            // Form passes JSON validation rules, perform custom validation:

            // Store session variable as global variable to give easy access
            // in custom_validation and validation_pass:
            $GLOBALS["form_validation_temporary_files"] = $_SESSION["form_validation_temporary_files"] ?? [];

            if ($custom_validation)
                $custom_validation();
        }

        if (!$GLOBALS["validation_invalid_feedback"]) {
            // Form passes all validation:

            // Forget the session variable:
            unset($_SESSION["form_validation_temporary_files"]);

            if ($validation_pass)
                $validation_pass();
        }
    }
}

// Marks field with name $name as invalid with given error message.
function invalidate($name, $message) {
    $GLOBALS["validation_invalid_feedback"][$name] = $message;
}

// Returns appropriate message for rule (with ruleName, ruleValue) of a field (with name):
function invalid_feedback_message($name, $rule_name, $rule_value) {
    $default_msg = (strpos($rule_name, "pattern") === 0) 
        ? ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_DEFAULT_MESSAGES"]["pattern"] ?? "") 
        : ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_DEFAULT_MESSAGES"][$rule_name] ?? "");
    $msg = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_MESSAGES"][$name][$rule_name] ?? $default_msg;
    $name_text = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_MESSAGES"][$name]["text"] ?? $name;
    $msg = str_replace("%1", $name_text, $msg);
    $s_rule_value = (is_array($rule_value) ? implode(", ", $rule_value) : $rule_value);
    return ucfirst(str_replace("%2", $s_rule_value, $msg));
}

// Returns the value of the input field, or selected file for file input:
function get_value($name) {
    $value = $_POST[$name] ?? "";
    if (!$value)
        $value = $_FILES[$name]["name"] ?? "";
    if (!$value)
        $value = $_SESSION["form_validation_temporary_files"][$name]["original"] ?? "";
    return $value;
}

// Validates all fields.
function json_validate() {
    $GLOBALS["validation_invalid_feedback"] = [];

    foreach ($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"] as $name => $rule) {
        $value = get_value($name);
        $store_file = false;    // Only used for files for which "store_file" is true.
        $combined_image_check = [];     // Combines all checks that require loading an image
        foreach ($rule as $rule_name => $rule_value) {
            $is_valid = true;

                    // if (empty($_FILES[$name]["name"]))
                    //     if (empty($_SESSION["form_validation_temporary_files"][$name]))

            if (($rule_name == "required") && ($rule_value))
                if (!$value)
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
                if (isset($_POST[$rule_value]))
                    if ($_POST[$rule_value] != $value)
                        $is_valid = false;

            if ($rule_name == "min_selected")
                if (count($value) < $rule_value)
                    $is_valid = false;

            if ($rule_name == "max_selected")
                if (count($value) > $rule_value)
                    $is_valid = false;

            if (isset($_FILES[$name]) && ($_FILES[$name]['error'] == UPLOAD_ERR_OK)) {
                // User is uploading another file - delete temporary file if we have one:
                delete_file_if_exists($_SESSION["form_validation_temporary_files"][$name]["temp_file"] ?? null);
                $_SESSION["form_validation_temporary_files"][$name] = null;

                $tmp_name = $_FILES[$name]['tmp_name'];
                $file_name = $_FILES[$name]['name'];
                $file_type = $_FILES[$name]["type"];
                $file_size = $_FILES[$name]['size'] / 1024.0 / 1024.0;

                if ($rule_name == "max_size_mb") 
                    if ($file_size > $rule_value)
                        $is_valid = false;

                if ($rule_name == "accept_extensions") {
                    $file_extension = "." . strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $file_type = strtolower($_FILES[$name]["type"]);
                    $is_match = false;
                    foreach ($rule_value as $extension) {
                        $extension = strtolower($extension);
                        if (substr($extension, 0, 1) == ".") {
                            // Extension looks like ".txt":
                            if ($extension === $file_extension) {
                                $is_match = true;
                                break;
                            }
                        }
                        if (substr($extension, -2) == "/*") {
                            // Extension looks like "image/*":
                            if (substr($file_type, 0, strlen($extension)-2) == substr($extension, 0, strlen($extension)-2)) {
                                $is_match = true;
                                break;
                            }
                        }
                        // Extension looks like "application/pdf":
                        if ($extension === $file_type) {
                            $is_match = true;
                            break;
                        }
                    }
                    if (!$is_match)
                        $is_valid = false;
                }

                if (($rule_name == "verify_is_image") && ($rule_value)) 
                    $combined_image_check["verify_is_image"] = $rule_value;

                if (($rule_name == "store_file") && ($rule_value))
                    $store_file = true;
            }
   

            if (!$is_valid) {
                $msg = invalid_feedback_message($name, $rule_name, $rule_value);
                invalidate($name, $msg);
                break;
            }
        }
        // Do image checks in one go:
        if (count($combined_image_check) > 0) {
            $img_size = @getimagesize($tmp_name);
            if (!$img_size) {
                $msg = invalid_feedback_message($name, "verify_is_image", true);
                invalidate($name, $msg);
            }
        }
        if (($store_file) && (!array_key_exists($name, $GLOBALS["validation_invalid_feedback"]))) {
            // "store file" is set true and field passes validation - store file
            store_file($name);
        }
    }
    // echo "<br>invalid-feedback: " . var_export($GLOBALS["validation_invalid_feedback"], true);
}

// Called after file upload validation to store the file temporarily:
function store_file($name) {
    if (!isset($_FILES[$name]) || ($_FILES[$name]['error'] != UPLOAD_ERR_OK))
        return;

    $tmp_name = $_FILES[$name]['tmp_name'];
    $file_name = $_FILES[$name]['name'];

    $destination_path = $GLOBALS["temporary_upload_directory"] . $name . "_" . bin2hex(random_bytes(6));
    if (move_uploaded_file($tmp_name, $destination_path)) {
        $_SESSION["form_validation_temporary_files"][$name] = ["original" => $file_name, "temp_file" => $destination_path];
    }
}

// Deletes a file:
function delete_file_if_exists($file_name) {
    if (!$file_name)
        return false;
    if (!file_exists($file_name))
        return false;
    if (!unlink($file_name))
        return false;
    return true;
}

// Used to add class for bootstrap to tell which inputs failed server-side validation.
function validation_class($name) {
    if (!$GLOBALS["validation_invalid_feedback"])
        return "";          // form is fresh - do nothing
    if (array_key_exists($name, $GLOBALS["validation_invalid_feedback"]))
        return "is-invalid";    // an error message exists for the field - invalid
    if (!array_key_exists($name, $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"]))
        return "";      // no rules for field - do not attempt to validate
    if (recall($name, false) != get_value($name))
        return "is-invalid";    // recall prevented and input nonempty - invalid  
    return "is-valid";  // default to valid
}

// Returns approppriate bootstrap text color for custom feedback div.
function validation_text_color($name) {
    $validation_class = validation_class($name);
    if ($validation_class == "is-invalid")
        return "text-danger";
    if ($validation_class == "is-valid")
        return "text-success";
    return "";
}

// Used to display server-side validation feedback
function custom_feedback($name) {
    if (array_key_exists($name, $GLOBALS["validation_invalid_feedback"]))
        return $GLOBALS["validation_invalid_feedback"][$name];
    if (!empty($_SESSION["form_validation_temporary_files"][$name])) {
        // Field is valid and the file has been uploaded and stored temporarily:
        $msg = invalid_feedback_message($name, "store_file", true);
        $msg = str_replace("%f", $_SESSION["form_validation_temporary_files"][$name]["original"], $msg);
        return $msg;
    }
    return "";
}

// Form data retention helper function:
function recall($name, $sanitize) {
    $prevent_recall = $GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"][$name]["prevent_recall"] ?? false;
    // Also prevent recall for file inputs when "store_file" is set false:
    $prevent_recall = $prevent_recall || !($GLOBALS["VALIDATION_JSON_DECODE"]["VALIDATION_RULES"][$name]["store_file"] ?? true);
    if ($prevent_recall)
        return "";
    $value = get_value($name);
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
function create_alert() {
    // Do not create an alert for a fresh form:
    if ($_SERVER["REQUEST_METHOD"] != "POST")
        return;

    $list = "";
    foreach ($GLOBALS["validation_invalid_feedback"] as $name => $value) 
        $list = $list . "<li>$value</li>";

    if ($list) {
        // Found a flaw - report the found flaws to the user
        echo <<<HTML
            <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="h5">Form submit failed:</div>
            HTML;
        echo $list;
        echo <<<HTML
            <button class="btn-close" aria-label="close" data-bs-dismiss="alert">
            </button></div>
            HTML;
    }
}

// Creates an alert that contains server-side information for debugging:
function create_debug_alert() {
    // make an alert that displays the input values:
    echo <<<HTML
        <div class="alert alert-primary alert-dismissible" role="alert">
        <div class="h5">Debug Info:</div>
        HTML;
    echo "<p>\$_POST: " . var_export($_POST, true) . "</p>";
    echo "<p>\$_FILES: " . var_export($_FILES, true) . "</p>";
    echo "<p>\$_SESSION: " . var_export($_SESSION, true) . "</p>";
    echo <<<HTML
        <button class="btn-close" aria-label="close" data-bs-dismiss="alert">
        </button></div>
        HTML;
}

?>