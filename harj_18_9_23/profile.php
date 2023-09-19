<?php 

session_start();

require "../sql_connect.php";
require "../config/pepper.php";     // obtains PEPPER for password hashing

$conn = connect("neilikka");

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
<body>
    <!-- Form Section -->
    <div class="container mt-5">
        <div class="jumbotron">
            <?php
                $username = htmlspecialchars($_SESSION["username"]);
                echo "<h1 class=\"display-4\">Hello, $username</h1>";
            ?>
            <p class="lead">Here is what we know about you:</p>

            <?php
            
            $username = $_SESSION["username"];
            $stmt = "SELECT * FROM users WHERE username=?";
            $result = substitute_and_execute($conn, $stmt, $username);
            if ($result["success"]) 
                if ($row = $result["value"]->fetch_assoc()) {
                    echo "Username: " . $row["username"] . "<br>";
                    echo "Full name: " . $row["fullname"] . "<br>";
                    echo "Email: " . $row["email"] . "<br>";
                    echo "Phone number: " . $row["phone"] . "<br>";
                    echo "Password hash: " . $row["pw_hash"] . "<br>";
                }
            ?>

            <hr class="my-4">
            <div class="row">
                <div class="col-6">
                    <a href="base.php" class="btn btn-primary">Back</a>
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="base.php?logout" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
