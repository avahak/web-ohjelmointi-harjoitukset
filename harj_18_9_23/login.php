<?php 

session_start();

require_once "../sql_connect.php";
require_once "../logs/logger.php";
require_once "db_operations.php";

$conn = new SqlConnection("web_admin_db");

$user_id = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $pw = $_POST["pw"] ?? "";
    $user_id = verify_password($conn, $email, $pw);
    if ($user_id) {
        $_SESSION["email"] = $email;
        if (isset($_POST["remember_me"])) 
            setup_remember_me($conn, $user_id);
        $target_page = $_SESSION["target_page"] ?? "base.php";
        header("Location: $target_page");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <link href="styles.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <div class="col-md-6 col-lg-4 container mt-5">
        <?php
        if (($_SERVER["REQUEST_METHOD"] == "POST") && (!$user_id)) {
            echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">";
            echo "<div class=\"h5\">Login failed, try again!</div>";
            echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
            echo "</button></div>";
        }
        ?>
        <div class="row justify-content-center">
            <div class="col">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="h3 mb-3">Login</div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" class="form-control" name="email" id="email">
                    </div>
                    <div class="form-group">
                        <label for="pw">Password:</label>
                        <input type="password" class="form-control" name="pw" id="pw">
                    </div>

                    <div class="row mt-3 mb-3">
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        <div class="col mt-1">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                    </div>
                    <a href="base.php">Back to frontpage.</a></br>
                    <a href="forgot_password.php">Forgot My Password</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>