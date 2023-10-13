<?php

// Sets up environment variables into $GLOBALS["config"].

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
    $config = [];
    foreach ($_ENV as $key => $value) {
        if (strpos($key, "APPSETTING_") === 0) 
            $config[substr($key, 11)] = $value;
    }
    if (empty($config))
        exit("Error reading AZURE environment variables.");
}


$GLOBALS["CONFIG"] = $config;

// Compare the following with Python's 'if __name__ == "__main__"':
$is_direct_request = str_ends_with(str_replace('\\', '/', __FILE__), $_SERVER['SCRIPT_NAME']);
if ($is_direct_request) {
    // echo "CONFIG: " . var_export($GLOBALS["CONFIG"], true);
    echo "<br>SQL_SERVER: " . $GLOBALS["CONFIG"]["SQL_SERVER"];
    echo "<br>SQL_PORT: " . $GLOBALS["CONFIG"]["SQL_PORT"];
    echo "<br>SITE: " . $GLOBALS["CONFIG"]["SITE"];
}

?>