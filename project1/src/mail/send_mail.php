<?php

if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "GOOGLE") 
    require_once __DIR__ . "/gmail_send.php";
else if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "MAILTRAP_IO") 
    require_once __DIR__ . "/mailtrap_send.php";

function send_mail() {
    if ($GLOBALS["CONFIG"]["ALLOW_EMAIL_SEND"] != "true")
        return;
    $args = func_get_args();
    if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "GOOGLE")
        call_user_func_array("gmail_send", $args);
    else if ($GLOBALS["CONFIG"]["EMAIL_SENDER"] == "MAILTRAP_IO")
        call_user_func_array("mailtrap_send", $args);
}

?>