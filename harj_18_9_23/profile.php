<?php 

require_once "../sql_connect.php";
require_once "user_operations.php";

$user_id = init_secure_page();
$user_data = user_data_from_id($user_id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessiot</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <!-- Form Section -->
    <div class="container text-dark mt-5">
        <div class="jumbotron">
            <?php
            $s_firstname = htmlspecialchars($user_data["firstname"]);
            $s_lastname = htmlspecialchars($user_data["firstname"]);
            $s_email = htmlspecialchars($user_data["email"]);
            $s_phone = htmlspecialchars($user_data["phone"]);
            $s_pw_hash = htmlspecialchars($user_data["pw_hash"]);
            echo "<h1 class=\"display-4\">Hello, $s_firstname!</h1>";
            ?>
            <p class="lead">Here is what we know about you:</p>

            <?php
            echo "Full name: $s_firstname $s_lastname. <br>";
            echo "Email: $s_email.<br>";
            echo "Phone number: $s_phone.<br>";
            echo "Password hash: $s_pw_hash.<br>";
            ?>

            <hr class="my-4">
            <div class="row mb-5">
                <div class="col-auto">
                    <a href="front.php?logout" class="btn btn-primary">Logout</a>
                </div>
                <div class="col-auto">
                    <a href="change_password.php" class="btn btn-primary">Change password</a>
                </div>
            </div>
            <div>
                <a href="front.php">Back to frontpage</a>
            </div>
        </div>
    </div>
</body>
</html>
