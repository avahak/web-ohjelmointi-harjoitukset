<?php 

// This page is used by logged in users to change passwords.
// Use ?.php for entering new password after password reset instead.

require_once "init.php";
require_once "user_operations.php";
require_once "template_pages.php";

$user_id = init_secure_page(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_data = user_data_from_id($user_id);
    if (verify_password($user_data["email"], $_POST["pw"])) {
        $result = change_password($user_id, $_POST["new_pw"]);
        echo template_change_password_result($result ? true : false);
        exit();
    } else {
        echo "POST - INCORRECT pw - invalidate";
        // TODO: invalidate pw field
    }
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
                    <div class="h3">Change password</div>
                    <p>Enter your current password and the new password twice to change your password.</p>
                    <div class="form-group">
                        <label for="username">Current password:</label>
                        <input type="password" class="form-control" name="pw" id="pw">
                    </div>
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