<?php

// Sets up environment variables into $GLOBALS["config"].

$is_localhost = in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
$is_azure = (strpos($_SERVER['HTTP_HOST'], 'azurewebsites') !== false);

$config = new stdClass();

// AZURE settings:
if ($is_azure) {
    $config = json_decode(getenv("APP_SETTINGS"), true);
    if (!$config)
        exit("Error reading AZURE environment variables.");
}

// localhost settings:
if ($is_localhost) {
    $config_file = "d:/resources/project1/localhost_settings.json";
    if (!file_exists($config_file))
        exit("Error! Missing localhost configuration file.");
    $config = json_decode(file_get_contents($config_file), true);
    if (!$config)
        exit("Error decoding localhost configuration file.");
}

$GLOBALS["CONFIG"] = $config;

// Compare the following with Python's 'if __name__ == "__main__"':
$is_direct_request = str_ends_with(str_replace('\\', '/', __FILE__), $_SERVER['SCRIPT_NAME']);
if ($is_direct_request) {
    // echo "CONFIG: " . var_export($GLOBALS["CONFIG"], true);
    echo "<br>SQL_SERVER: " . $GLOBALS["CONFIG"]["SQL_SERVER"];
    echo "<br>SQL_PORT: " . $GLOBALS["CONFIG"]["SQL_PORT"];
}

?>