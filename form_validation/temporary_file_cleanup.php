
<?php

// Creates my_temporary_files directory if it does not exist. 
// Also removes any files it contains.

// Create my_temporary_files directory under web root if it does not exist yet:
echo "<br>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'];
$temp_dir = $_SERVER['DOCUMENT_ROOT'] . "/my_temporary_files/";
if (empty($temp_dir))
    exit("<br>ERROR: \$temp_dir is empty.");
echo "<br>Making sure that directory $temp_dir exists and does not contain files.";

if (!file_exists($temp_dir))
    mkdir($temp_dir, 0777, true);

// Delete all files inside this directory:
$files_to_delete = glob($temp_dir . "*");
foreach ($files_to_delete as $file) {
    echo "<br>Deleting: $file";
    if (is_file($file))
        unlink($file);
}

?>