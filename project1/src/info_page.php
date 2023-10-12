<?php 

require_once "shared_elements.php";
require_once "init.php";

init();

shared_script_start("Info");
?>

<div class="container">
    <?php
    if (isset($_SESSION["user_id"]))
        echo "user_id session variable:" . htmlspecialchars($_SESSION["user_id"]) . "</br>";
    else 
        echo "user_id session variable: not set.</br>";
    if (isset($_SESSION["target_page"]))
        echo "target_page session variable:" . htmlspecialchars($_SESSION["target_page"]) . "</br>";
    else 
        echo "target_page session variable: not set.</br>";
    if (isset($_COOKIE["remember_me"]))
        echo "remember_me cookie:" . htmlspecialchars($_COOKIE["remember_me"]) . "</br>";
    else 
        echo "remember_me cookie: not set.</br>";
    ?>

</div>

<?php shared_script_end(); ?>
