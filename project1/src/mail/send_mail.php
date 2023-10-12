<?php

// Uses gmail or mailtrap to send email, according to:
define("SEND_ACTUAL_EMAIL", false);

if (SEND_ACTUAL_EMAIL) 
    require_once __DIR__ . "/gmail_send.php";
else 
    require_once __DIR__ . "/mailtrap_send.php";

function send_mail() {
    $args = func_get_args();
    if (SEND_ACTUAL_EMAIL) 
        call_user_func_array("gmail_send", $args);
    else 
        call_user_func_array("mailtrap_send", $args);
}

?>