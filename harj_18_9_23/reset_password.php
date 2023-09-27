<?php 

// This page is used for entering a new password after a password 
// reset request has been submitted. 
// Use change_password.php for changing user password while logged in.

require_once "init.php";
require_once "user_operations.php";
require_once "template_pages.php";

init();

function verify_reset_password_url_key() {
    if (!isset($_GET["key"]))
        return false;
    $key = $_GET["key"];
    $selector = substr($key, 0, 16);
    $validator = substr($key, 16);
    $user_id = verify_token($selector, $validator, "RESET_PASSWORD", true);
    $GLOBALS["g_logger"]->debug("Password reset in progress.", ["selector" => $selector, "validator" => $validator, "key" => $key, "user_id" => $user_id]);
    return $user_id;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // here we get the key and from it the user:
    $user_id = verify_reset_password_url_key();
    if (!$user_id) {
        echo template_invalid_reset_password_link();
        exit();
    }
    // We need to pass user_id from here at GET to later at POST:
    $_SESSION["reset_password_user_id"] = $user_id;
    echo "</br>TOKEN WAS VALID - user_id: $user_id";
}

echo "</br>Session reset_password_user_id: " . ($_SESSION["reset_password_user_id"] ?? "falsy");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // read and unset the session variable:
    $user_id = $_SESSION["reset_password_user_id"];
    unset($_SESSION["reset_password_user_id"]);

    $new_pw = $_POST["new_pw"];
    $GLOBALS["g_logger"]->warning("reset_password POST changing pw", compact("user_id", "new_pw"));
    change_password($user_id, $_POST["new_pw"]);
    echo template_change_password_result(true);
    exit();
}

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
    <div class="container mt-5 text-dark" style="max-width:400px">
        <div class="row justify-content-center">
            <div class="col">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="h3">Reset password</div>
                    <p>Enter the new password twice to reset your password.</p>
                    <div class="form-group">
                        <label for="new_pw">New password:</label>
                        <input type="password" class="form-control" name="new_pw" id="new_pw">
                    </div>
                    <div class="form-group">
                        <label for="new_pw2">New password confirmed:</label>
                        <input type="password" class="form-control" name="new_pw2" id="new_pw2">
                    </div>
                    <div class="col-auto mt-3 mb-4">
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                    <div>
                        <a href="front.php">Back to frontpage</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>