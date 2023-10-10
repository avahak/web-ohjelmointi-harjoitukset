<?php

function create_thumbnail($original_path, $thumbnail_path, $max_size=128) {
    $original = @imagecreatefromjpeg($original_path);
    if (!$original)
        return false;

    // Get the original image dimensions
    $o_width = imagesx($original);
    $o_height = imagesy($original);

    // Calculate the thumbnail dimensions while maintaining the aspect ratio
    if ($o_width > $o_height) {
        $t_width = $max_size;
        $t_height = ($o_height / $o_width) * $max_size;
    } else {
        $t_height = $max_size;
        $t_width = ($o_width / $o_height) * $max_size;
    }

    // Create a blank thumbnail image
    $thumbnail = imagecreatetruecolor($t_width, $t_height);

    // Resize and copy the original image to the thumbnail
    imagecopyresampled($thumbnail, $original, 0, 0, 0, 0, $t_width, $t_height, $o_width, $o_height);

    // Save the thumbnail as a new JPEG image
    $result = imagejpeg($thumbnail, $thumbnail_path);

    // Clean up resources
    imagedestroy($originalImage);
    imagedestroy($thumbnailImage);

    return $result;
}

// create_thumbnail("C:/Users/mavak/Desktop/sun/GQCL5CH7BRFM3IOJP4PVBSGCRI.avif", "C:/Users/mavak/Desktop/sun/thumbnail.jpg")
// phpinfo();
if (function_exists('gd_info')) {
    echo 'GD is enabled';
    echo '<pre>';
    print_r(gd_info());
    echo '</pre>';
} else {
    echo 'GD is not enabled';
}
if (extension_loaded('gd')) {
    echo 'GD is enabled';
} else {
    echo 'GD is not enabled';
}

?>
