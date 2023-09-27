<?php

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "user_operations.php";

// Initialized $g_conn and $g_logger if not yet done. Starts a session if not started.
function init() {
    global $g_conn;
    global $g_logger;

    if (!isset($g_conn))
        $g_conn = new SqlConnection("web_admin_db");
    if (!isset($g_logger))
        $g_logger = new Logger();

    if(!session_id())
        session_start();

    if (isset($_COOKIE["remember_me"]) && (!isset($_SESSION["user_id"]))) 
        authenticate_user(false);
}

// Used to initialize secure pages. Redirects user to login page if not logged in yet.
// Returns $user_id if authentication succeeded.
function init_secure_page() {
    init();
    return authenticate_user(true);
}

?>
