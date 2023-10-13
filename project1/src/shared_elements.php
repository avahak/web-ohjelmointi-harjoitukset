<?php

require_once __DIR__ . "/debug_element.php";

// Beginning of the shared script:
function shared_script_start($title) {
    ob_start(); ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
        <title><?= $title ?></title>

        <?php include_bootstrap(); ?>

        <link rel="stylesheet" href="shared_styles.css">
    </head>

    <body>
        <?php include_navbar(); ?>

    <?php ob_end_flush();
}

// Ending of the shared script:
function shared_script_end() {
    include_footer();
    include_debug_div();
    echo "</body></html>";
}

// Call in header to include bootstrap css and js.
function include_bootstrap() {
    echo <<<HTML
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
        HTML;
}

// Code for top menu:
function include_navbar() {
    ob_start(); 
    $active_page = (basename($_SERVER["SCRIPT_NAME"]) ?? ""); 
    ?>

    <nav class="navbar navbar-expand-sm navbar-dark p-0">
        <div class="container d-flex px-3 py-1" style="overflow:hidden;">

            <button class="navbar-toggler order-1" data-bs-toggle="collapse" data-bs-target="#nav">
                <div class="navbar-toggler-icon"></div>
            </button>

            <a href="front.php" class="navbar-brand order-2" style="height:0;">
                <img src="../images/tba.png" style="width:120px;margin-top:-30px;" alt="TBA">
            </a>

            <ul class="navbar-nav order-3 order-sm-5">
                <?php
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["user_id"])) {
                        echo "<a href=\"front.php?logout\" class=\"nav-link\">Log out</a>";
                    } else {
                        echo "<a href=\"login.php\" class=\"nav-link " . ($active_page == "login.php" ? "active" : "") . "\">Log in</a>";
                    }
                    echo "</li>";
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["user_id"])) {
                        echo "<a href=\"profile.php\" class=\"nav-link " . ($active_page == "profile.php" ? "active" : "") . "\">Profile</a>";
                    } else {
                        echo "<a href=\"signup.php\" class=\"nav-link " . ($active_page == "signup.php" ? "active" : "") . "\">Sign up</a>";
                    }
                    echo "</li>";
                ?>
            </ul>

            <div class="collapse navbar-collapse order-4" id="nav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="#" class="nav-link">Info</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Shots</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Other</a>
                    </li>
                    <?php
                        if (isset($_SESSION["user_id"])) {
                            echo "<li class=\"nav-item\">";
                            echo "<a href=\"#\" class=\"nav-link\">Confidential</a>";
                            echo "</li>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php ob_end_flush();
}

// Adds footer to the page.
function include_footer() {
    ob_start(); ?>

    <footer class="custom-footer" id="custom-footer">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-6 text-center text-sm-left">
                    <p class="m-0">&copy; 2023 Aleksi V</p>
                </div>
                <div class="col-12 col-sm-6 text-center text-sm-right">
                    <a class="link-primary" href="./contact.php">Contact Us</a>
                    <span class="separator">|</span>
                    <a class="link-primary" href="./privacy.php">Privacy Statement</a>
                </div>
            </div>
        </div>
    </footer>

    <?php ob_end_flush();
}

?>