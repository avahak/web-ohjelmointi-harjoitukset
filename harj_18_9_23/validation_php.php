<?php

define('VALIDATION_JSON_STRING', file_get_contents('validation_rules.json'));
$json_array = json_decode(VALIDATION_JSON_STRING, true);
define('VALIDATION_DEFAULT_MESSAGES', $json_array['VALIDATION_DEFAULT_MESSAGES']);
define('VALIDATION_RULES', $json_array['VALIDATION_RULES']);
define('VALIDATION_MESSAGES', $json_array['VALIDATION_MESSAGES']);

// VALIDATION_DEFAULT_MESSAGES are default messages used when no specific message 
// is defined in VALIDATION_MESSAGES:

// VALIDATION_RULES define the restrictions placed on the input fields

// VALIDATION_MESSAGES are custom messages for validation failure communication. 
// %1 refers to field name text, %2 to value of the rule

// VALIDATION_TRIGGERS is used by javascript to validate other fields in addition
// to the one changed (used with force_equality).

// Validates all fields whose name is in $arr.
function validate($arr) {
    $messages = [];
    foreach (VALIDATION_RULES as $name => $rule) {
        $value = $arr[$name] ?? "";
        foreach ($rule as $rule_name => $rule_value) {
            $is_invalid = false;

            if (($rule_name == "required") && ($rule_value))
                if (strlen($value) == 0)
                    $is_invalid = true;

            if ($rule_name == "min_length")
                if (strlen($value) < $rule_value)
                    $is_invalid = true;

            if ($rule_name == "max_length")
                if (strlen($value) > $rule_value)
                    $is_invalid = true;

            if (strpos($rule_name, "pattern") === 0)     // $rule_name starts with "pattern"
                if (!preg_match("/" . $rule_value . "/", $value))
                    $is_invalid = true;

            if (($rule_name == "numeric") && ($rule_value))
                if (!is_numeric($value))
                    $is_invalid = true;

            if ($rule_name == "min")
                if ($value < $rule_value)
                    $is_invalid = true;
        
            if ($rule_name == "max")
                if ($value > $rule_value)
                    $is_invalid = true;

            if ($rule_name == "force_equality")
                if (isset($arr[$rule_value]))
                    if ($arr[$rule_value] != $value)
                        $is_invalid = true;

            if ($is_invalid) {
                $default_msg = (strpos($rule_name, "pattern") === 0) ? VALIDATION_DEFAULT_MESSAGES["pattern"] : VALIDATION_DEFAULT_MESSAGES[$rule_name];
                $msg = VALIDATION_MESSAGES[$name][$rule_name] ?? $default_msg;
                $name_text = VALIDATION_MESSAGES[$name]['text'] ?? $name;
                $msg = str_replace("%1", $name_text, $msg);
                $msg = str_replace("%2", $rule_value, $msg);
                $messages[$name] = ucfirst($msg) . ".";
                break;
            }
        }
    }
    return $messages;
}

?>