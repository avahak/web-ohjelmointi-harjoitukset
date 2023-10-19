<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../../sql_connect.php";
require_once __DIR__ . "/../../logs/logger.php";
require_once __DIR__ . "/user_operations.php";

// TODO not tested
function set_error_reporting() {
    if ($GLOBALS["CONFIG"]["IS_PRODUCTION_SETTING"] == "true") {
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
        ini_set('display_errors', 0);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    ini_set('log_errors', 1);
    ini_set('error_log', '../../logs/php_errors.log');
}

// Initialized g_conn and g_logger if not yet done. Starts a session if not started.
// TODO instead of global variables, could use session variables?
function init() {
    set_error_reporting();

    if (!isset($GLOBALS["g_conn"]))
        $GLOBALS["g_conn"] = new SqlConnection("tba_db");
    if (!isset($GLOBALS["g_logger"]))
        $GLOBALS["g_logger"] = new Logger();

    if(!session_id())
        session_start();

    if (isset($_SESSION["INVALIDATE_REMEMBER_ME"])) {
        setcookie("remember_me", "", time() - 3600);
        unset($_SESSION["INVALIDATE_REMEMBER_ME"]);
    } else if (isset($_COOKIE["remember_me"]) && (!isset($_SESSION["user_id"]))) 
        authenticate_user(false);
}

// Used to initialize secure pages. Redirects user to login page if not logged in yet.
// Returns $user_id if authentication succeeded.
function init_secure_page() {
    init();
    return authenticate_user(true);
}

?>