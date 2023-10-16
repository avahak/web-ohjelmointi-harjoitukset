<?php

if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "GOOGLE") 
    require_once __DIR__ . "/gmail_send.php";
else 
    require_once __DIR__ . "/mailtrap_send.php";

function send_mail() {
    $args = func_get_args();
    if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "GOOGLE")
        call_user_func_array("gmail_send", $args);
    else 
        call_user_func_array("mailtrap_send", $args);
}

?>