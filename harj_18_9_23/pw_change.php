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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">


                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $virhe = "";
                    $username = isset($_POST["username"]) ? $_POST["username"] : "";
                    $pw = isset($_POST["pw"]) ? $_POST["pw"] : "";
                    $verify = verify_password($conn, $username, $pw);
                    if (!$verify)
                        $virhe = "Current password is wrong.";
                    else {
                        $new_pw = isset($_POST["new_pw"]) ? $_POST["new_pw"] : "";
                        $new_pw2 = isset($_POST["new_pw2"]) ? $_POST["new_pw2"] : "";
                        if $
                    }
                }
                ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <fieldset>
                        <legend>Change password</legend>
                        <div class="form-group">
                            <label for="username">Current password:</label>
                            <input type="pw" class="form-control" name="pw" id="pw">
                        </div>
                        <div class="form-group">
                            <label for="new_pw">New password:</label>
                            <input type="password" class="form-control" name="new_pw" id="new_pw">
                        </div>
                        <div class="form-group">
                            <label for="new_pw2">New password confirmed:</label>
                            <input type="password" class="form-control" name="new_pw2" id="new_pw2">
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>