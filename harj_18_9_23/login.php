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
                // Here we get form POST data and it should be checked  (server side validation) 

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = isset($_POST["username"]) ? $_POST["username"] : "";
                    $pw = isset($_POST["pw"]) ? $_POST["pw"] : "";
                    $hash = false;
                    // echo "Username, pw: $username, $pw<br>";
                    if ($username) {
                        $stmt = "SELECT * FROM users WHERE username=?";
                        $result = substitute_and_execute($conn, $stmt, $username);
                        if ($result["success"]) 
                            if ($row = $result["value"]->fetch_assoc())
                                if (isset($row["pw_hash"]))
                                    $hash = $row["pw_hash"];
                    }
                    $verify = $hash ? password_verify(PEPPER . $pw, $hash) : false;
                    // echo "Hash: $hash<br>";
                    // echo "Verify: " . ($verify ? "TRUE" : "FALSE") . "<br>";

                    if ($verify) {
                        header("Location: base.php");
                        $_SESSION["username"] = $username;
                        exit();
                    } else {
                        echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">";
                        echo "<div class=\"h5\">Login failed, try again!</div>";
                        echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                        echo "</button></div>";
                    }
                }

                ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <fieldset>
                        <legend>Login</legend>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" name="username" id="username">
                        </div>
                        <div class="form-group">
                            <label for="pw">Password:</label>
                            <input type="password" class="form-control" name="pw" id="pw">
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>