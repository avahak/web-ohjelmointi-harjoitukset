<?php

function create_thumbnail($original_path, $thumbnail_path, $max_size=128) {
    $s_file = file_get_contents($original_path);
    $original = imagecreatefromstring($s_file);
    if (!$original)
        return false;
    // Creating an image from a string like this feels wrong and inefficient but 
    // this seems like the easiest general way to do this with GD library.

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
    imagedestroy($original);
    imagedestroy($thumbnail);

    return $result;
}

// $result = create_thumbnail("C:/Users/mavak/Desktop/sun/6938796248_27574ee44c_b.jpg", "C:/Users/mavak/Desktop/sun/thumbnail.jpg");
// $result = create_thumbnail("C:/Users/mavak/Desktop/sun/GQCL5CH7BRFM3IOJP4PVBSGCRI.avif", "C:/Users/mavak/Desktop/sun/thumbnail.jpg");
// $result = create_thumbnail("C:/Users/mavak/Desktop/sun/Sun_poster.svg.png", "C:/Users/mavak/Desktop/sun/thumbnail.jpg");
$result = create_thumbnail("C:/Users/mavak/Desktop/sun/screen_shot_2015-11-05_at_122320_pm.webp", "C:/Users/mavak/Desktop/sun/thumbnail.jpg");
echo ($result ? "Thumbnail created." : "Thumbnail creation failed.") . "<br>";
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
