<?php 
    session_start();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessiot</title>
    <!-- Lisää Bootstrap CSS-tiedosto -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <!-- Lisää oma CSS-tyylitiedosto -->
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <!-- Form Section -->
    <div class="container mt-5">
        <div class="jumbotron">
            <?php
                $username = $_SESSION["username"];
                echo "<h1 class=\"display-4\">Terve, $username</h1>";
            ?>
            <p class="lead">Tämä on sinun profiilisivusi.</p>
            <hr class="my-4">
            <div class="row">
                <div class="col-6">
                    <a href="sessiot.php" class="btn btn-primary">Takaisin</a>
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="sessiot.php?logout=true" class="btn btn-danger">Kirjaudu ulos</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
