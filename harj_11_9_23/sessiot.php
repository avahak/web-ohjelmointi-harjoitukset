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
    <link href="lemmikit/styles.css" rel="stylesheet">
</head>
<body>
    <?php
        if (($_SERVER["REQUEST_METHOD"] == "GET") && (isset($_GET["logout"]))) {
            // using session_unset() takes immediate effect, unlike session_destroy() alone
            session_unset();
            session_destroy();
        }
        else if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $avain = isset($_POST["avain"]) ? $_POST["avain"] : "";
            $arvo = isset($_POST["arvo"]) ? $_POST["arvo"] : "";
            if (($avain) && ($arvo)) {
                $_SESSION[("avain_" . $avain)] = $arvo;
            }
        }
    ?>

    <!-- Form Section -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <fieldset>
                        <legend>Lisää pareja</legend>
                        <div class="form-group">
                            <label for="avain">Avain:</label>
                            <input type="text" class="form-control" name="avain" id="avain">
                        </div>
                        <div class="form-group">
                            <label for="arvo">Arvo:</label>
                            <input type="text" class="form-control" name="arvo" id="arvo">
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary">Lisää</button>
                </form>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h3 class="text-center">Sessioparametrit</h3>
            </div>
            <div class="col-md-4 d-flex justify-content-end">
                <a href="sessiot.php?logout=true" class="btn btn-danger">Tuhoa kaikki</a>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php
                    if ($_SESSION) {
                        foreach ($_SESSION as $x => $arvo) {
                            $avain = substr($x, 6);
                            echo $avain . ": " . $arvo . "<br>";
                        }
                    } else {
                        echo "Tyhjä sessio.";
                    }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
