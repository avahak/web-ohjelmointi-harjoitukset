<?php 

session_start();

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "db_operations.php";

$conn = new SqlConnection("web_admin_db");

if (($_SERVER["REQUEST_METHOD"] == "GET") && (isset($_GET["logout"]))) {
    // using session_unset() takes immediate effect, unlike session_destroy() alone
    session_unset();
    session_destroy();
    // clear the remember me cookie too in case it was set:
    setcookie("remember_me", "", time() - 3600);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
    <title>Sessiot</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <style>
        body {background-color: #777;}
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container d-flex">
            <button class="navbar-toggler order-1" data-bs-toggle="collapse" data-bs-target="#nav">
                <div class="navbar-toggler-icon"></div>
            </button>

            <a href="base.php" class="navbar-brand order-2">Frontpage</a>

            <ul class="navbar-nav order-3 order-md-5">
                <?php
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["email"])) {
                        echo "<a href=\"base.php?logout\" class=\"nav-link\">Log out</a>";
                    } else {
                        echo "<a href=\"signup.php\" class=\"nav-link\">Sign up</a>";
                    }
                    echo "</li>";
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["email"])) {
                        echo "<a href=\"profile.php\" class=\"nav-link\">Profile</a>";
                    } else {
                        echo "<a href=\"login.php\" class=\"nav-link\">Log in</a>";
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
                    <?php
                        if (isset($_SESSION["email"])) {
                            echo "<li class=\"nav-item\">";
                            echo "<a href=\"#\" class=\"nav-link\">Confidential</a>";
                            echo "</li>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        Content here..<br>
        <?php
        if (isset($_SESSION["email"]))
            echo "email session variable:" . htmlspecialchars($_SESSION["email"]) . "</br>";
        else 
            echo "email session variable is not set.</br>";
        if (isset($_COOKIE["remember_me"]))
            echo "remember_me cookie value:" . htmlspecialchars($_COOKIE["remember_me"]) . "</br>";
        else 
            echo "remember_me cookie is not set.</br>";
        ?>
    </div>
</body>

</html>
