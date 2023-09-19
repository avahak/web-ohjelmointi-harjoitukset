<?php 
    session_start();
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
    <?php
        if (($_SERVER["REQUEST_METHOD"] == "GET") && (isset($_GET["logout"]))) {
            // using session_unset() takes immediate effect, unlike session_destroy() alone
            session_unset();
            session_destroy();
        }
        // if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //     $username = isset($_POST["username"]) ? $_POST["username"] : "";
        //     $pw = isset($_POST["pw"]) ? $_POST["pw"] : "";
        //     if (($username) && ($pw)) {
        //         $_SESSION["username"] = $username;
        //     }
        // }
    ?>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container d-flex">
            <button class="navbar-toggler order-1" data-bs-toggle="collapse" data-bs-target="#nav">
                <div class="navbar-toggler-icon"></div>
            </button>

            <a href="#" class="navbar-brand order-2">Frontpage</a>

            <ul class="navbar-nav order-3 order-md-5">
                <?php
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["username"])) {
                        echo "<a href=\"base.php?logout\" class=\"nav-link\">Logout</a>";
                    } else {
                        echo "<a href=\"register.php\" class=\"nav-link\">Registration</a>";
                    }
                    echo "</li>";
                    echo "<li class=\"nav-item\">";
                    if (isset($_SESSION["username"])) {
                        echo "<a href=\"profile.php\" class=\"nav-link\">Profile</a>";
                    } else {
                        echo "<a href=\"login.php\" class=\"nav-link\">Login</a>";
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
                        if (isset($_SESSION["username"])) {
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
        Content here..
    </div>
</body>

</html>
