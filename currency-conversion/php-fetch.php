<?php
// here we obtain the hidden API_KEY
// config.php contains php tags and line define('API_KEY', '...'), 
// where '...' is text so that url defined below works
include '../config/config.php';

// other good way to hide the api key would be to store the key in 
// an environment variable and then use $apiKey = getenv('API_KEY');

// Check if a 'base' parameter is provided in the query string
if (isset($_GET['base'])) {
    // Retrieve value from the 'base' parameter
    $base = $_GET['base'];

    $url = "https://" . API_KEY . $base;
    // $url = "json/" . $base . ".json";        // just for testing

    // echo "'" . $url . "'";
    $response = @file_get_contents($url);

    if ($response === false) {
        $response = json_encode(['error' => 'problem loading url']);
        echo $response;
    } else {
        echo $response;
    }
} else {
    $response = json_encode(['error' => 'missing base']);
    echo $response;
}
?>