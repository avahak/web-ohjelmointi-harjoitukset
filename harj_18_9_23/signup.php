<?php 

// use this to output some extra info for debugging:
define('DEBUG_MODE', 1);

require_once "../sql_connect.php";
require_once "validation_php.php";
require_once "user_operations.php";
require_once "send_mail.php";
require_once "tokens.php";
require_once "template_pages.php";

init();

// form data retention helper function:
function recall($name, $sanitize) {
    if ($sanitize)
        return isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : "";
    return isset($_POST[$name]) ? $_POST[$name] : "";
}

// used to add class for bootstrap to tell which inputs failed server-side validation
function add_validation_class($field) {
    global $is_invalid_list;
    if (empty($is_invalid_list))    // form is fresh - do nothing
        return;
    echo (array_key_exists($field, $is_invalid_list) ? "is-invalid" : "is-valid");
    return;
}

// Used to replace generic client-side validation error 
// with a more specific server-side validation error
function custom_feedback($field) {
    global $is_invalid_list;
    if (array_key_exists($field, $is_invalid_list))
        echo $is_invalid_list[$field];
}

// list of input field names that fail server side validation:
$is_invalid_list = [];

// Here we get form POST data and it should be checked  (server side validation) 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // obtain the POST data:
    $firstname = recall("firstname", false);
    $lastname = recall("lastname", false);
    $email = recall("email", false);
    $phone = recall("phone", false);
    $pw = recall("pw", false);
    $pw2 = recall("pw2", false);
    // Server-side validation:

    $is_invalid_list = validate($_POST);

    if (!$is_invalid_list) {
        // add user to database here..

        if (!user_id_from_email($email)) {
            $result = add_user($firstname, $lastname, $email, $phone, $pw);
            if ($result["success"]) { 
                // send verification email and tell user about it:
                $user_id = $GLOBALS["g_conn"]->get_connection()->insert_id;
                $fullname = $firstname . " " . $lastname;
                $token = create_token($user_id, "EMAIL_VERIFICATION", 24);
                $key = urlencode($token["selector"] . $token["validator"]);

                $GLOBALS["g_logger"]->debug("Adding new user", ["user_id" => $user_id, "key" => $key, "fullname" => $fullname, "email" => $email]);

                send_mail("Email verification link", email_template_verification_email($key), 
                        "Webteam", $email, $fullname, true);
                echo template_signup_success($email);
                exit(); 
            } else {
                // insert failed
                $is_invalid_list[] = "Adding user failed: " . $result['value'];
            }
        } else {
            $is_invalid_list["email"] = "Email is already used.";
        }
    }
    if (!$is_invalid_list) {
        // $_SESSION["email"] = $email;
        header("Location: front.php");
        // echo "<script>window.location.href=\"front.php\";</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration form</title>
    
    <!-- Bootstrap: -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <link href="styles.css" rel="stylesheet">
    <?php echo "<script>const VALIDATION_JSON = JSON.parse(" . json_encode(VALIDATION_JSON_STRING, JSON_HEX_APOS | JSON_HEX_QUOT) . ");</script>"; ?>
    <!-- <?php echo "<script>console.log(VALIDATION_JSON);</script>"; ?> -->
    <script src="validation_js.js"></script>
</head>
<body class="bg-dark text-light">
    <div class="container mt-2 p-auto">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (DEBUG_MODE) {     
                // make an alert that displays the input values:
                echo "<div class=\"alert alert-primary alert-dismissible\" role=\"alert\">";
                echo "<div class=\"h5\">[DEBUG] Form input:</div>";
                echo "First name: " . $firstname . "<br>";
                echo "Last name: " . $lastname . "<br>";
                echo "email: " . $email . "<br>";
                echo "phone: " . $phone . "<br>";
                echo "Password: " . $pw . "<br>";
                echo "Password (confirmation): " . $pw2 . "<br>";
                echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                echo "</button></div>";
            }

            if ($is_invalid_list) {
                // found a flaw - report the found flaws to the user
                echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">";
                echo "<div class=\"h5\">ERROR:</div>";
                foreach ($is_invalid_list as $name => $value) 
                    echo "<li>$value</li>";
                echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                echo "</button></div>";
            }
        }
        ?>

        <div class="row m-2">
            <h1>Registration <?php if (DEBUG_MODE) echo "[DEBUG MODE]"; ?></h1>
            <!-- form starts here -->
            <form id="my_form" class="needs-validation <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" ? "was-validated" : ""); ?>" novalidate method="POST">
                <div class="row my-3">

                    <?php $field = "firstname"; ?>
                    <div class="col-sm-6">
                        <label for="firstname" class="form-label">First name:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="firstname" name="firstname" placeholder="First name" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="firstname-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "lastname"; ?>
                    <div class="col-sm-6">
                        <label for="lastname" class="form-label">Last name:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="lastname" name="lastname" placeholder="Last name" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="lastname-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">

                    <?php $field = "email"; ?>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control <?php add_validation_class($field); ?>" id="email" name="email" placeholder="Enter email address" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="email-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "phone"; ?>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label">Phone number:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="phone" name="phone" placeholder="Phone number" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="phone-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">

                    <?php $field = "pw"; ?>
                    <div class="col-sm-6">
                        <label for="pw" class="form-label">Password:</label>
                        <input type="password" class="form-control <?php add_validation_class($field); ?>" id="pw" name="pw" placeholder="Enter password">
                        <div class="invalid-feedback" id="pw-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "pw2"; ?>
                    <div class="col-sm-6">
                        <label for="pw2" class="form-label">Password confirmation:</label>
                        <input type="password" class="form-control <?php add_validation_class($field); ?>" id="pw2" name="pw2" placeholder="Retype your password">
                        <div class="invalid-feedback" id="pw2-feedback">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>

                <!-- Submit -->
                <button type="submit" name="submit" id="submit" class="btn btn-primary btn-lg">Submit</button>
            </form>
            <p class="mt-5"><a href="front.php">Back to frontpage</a></p>
        </div>
    </div>
</body>
</html>