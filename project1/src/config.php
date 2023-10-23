<?php

// Sets up environment variables into $GLOBALS["config"].
function setup_globals_config() {
    $is_localhost = in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
    $is_azure = (strpos($_SERVER['HTTP_HOST'], 'azurewebsites') !== false);

    $config = new stdClass();

    // localhost settings:
    if ($is_localhost) {
        $config_file = "d:/projects/project1/localhost_settings.json";
        if (!file_exists($config_file))
            $config_file = "c:/projects/project1/localhost_settings.json";
        if (!file_exists($config_file))
            exit("Error! Missing localhost configuration file.");
        $config = json_decode(file_get_contents($config_file), true);
        if (!$config)
            exit("Error decoding localhost configuration file.");
    }

    // AZURE settings:
    if ($is_azure) {
        // exit("Temporarily offline");    // TODO REMOVE!
        $config = [];
        foreach ($_ENV as $key => $value) {
            if (strpos($key, "APPSETTING_") === 0) 
                $config[substr($key, 11)] = $value;
        }
        if (empty($config))
            exit("Error reading AZURE environment variables.");
    }

    $GLOBALS["CONFIG"] = $config;
    $GLOBALS["CONFIG"]["IS_LOCALHOST"] = $is_localhost;
}

// Set up error message logging
function setup_error_handling($error_log_file) {
    error_reporting(E_ALL & ~E_NOTICE);
    // error_reporting(E_ALL);
    if ($GLOBALS["CONFIG"]["IS_PRODUCTION_SETTING"])
        ini_set('display_errors', 0);
    else 
        ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', $error_log_file); // Replace with your log file path

    $max_file_size = 2 * 1024 * 1024; // 2MB
    if (file_exists($error_log_file) && filesize($error_log_file) > $max_file_size) {
        // If the log file is too large, trim it
        $log_data = file_get_contents($log_file);
        $log_data = substr($log_data, -ceil(0.1*$max_file_size)); // leave 10% of data
        file_put_contents($log_file, $log_data);
    }

    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null && $error['type'] === E_ERROR)
            error_log("Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
    });
}

setup_globals_config();
setup_error_handling(__DIR__ . "/../../logs/php_errors.log");
// setup_error_handling("c:/xampp/htdocs/kurssi/logs/php_errors.log");

// trigger_error("YYYYY", E_USER_ERROR);

// Compare the following with Python's 'if __name__ == "__main__"':
$is_direct_request = str_ends_with(str_replace('\\', '/', __FILE__), $_SERVER['SCRIPT_NAME']);
if ($is_direct_request) {
    // echo "CONFIG: " . var_export($GLOBALS["CONFIG"], true);
    echo "<br>IS_PRODUCTION_SETTING: " . $GLOBALS["CONFIG"]["IS_PRODUCTION_SETTING"];
    echo "<br>SQL_SERVER: " . $GLOBALS["CONFIG"]["SQL_SERVER"];
    echo "<br>SQL_PORT: " . $GLOBALS["CONFIG"]["SQL_PORT"];
    echo "<br>SITE: " . $GLOBALS["CONFIG"]["SITE"];
}

?>