<?php 

session_start();

require "../sql_connect.php";
require "../config/pepper.php";     // obtains PEPPER for password hashing

// Password hashing:
// $hash = password_hash(PEPPER . $pw, PASSWORD_DEFAULT);
// This $hash includes random salt and can be directly stored in database.
// Does $pw_another match original $pw? Use: password_verify(PEPPER . $pw_another, $hash)

$conn = connect("neilikka");

// password: at least X characters, contains at least one alphanumeric character 
// and at least one non-alphanumeric character
define('PW_PATTERN', '/^(?=.*[A-Za-z0-9])(?=.*[^A-Za-z0-9]).{3,}$/');

// use this to output some extra info for debugging:
define('DEBUG_MODE', 1);

// form data retention helper function:
function recall($name, $sanitize) {
    if ($sanitize)
        return isset($_POST[$name]) ? sanitize_for_html($_POST[$name]) : "";
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
    global $field_names;
    global $is_invalid_list;
    if (array_key_exists($field, $is_invalid_list))
        echo "Invalid " . $field_names[$field] . ": " . $is_invalid_list[$field] . ".";
}

// associate a text description for each input field name:
$field_names = ["username" => "username", 
    "fullname" => "full name",
    "email" => "email", 
    "phone" => "phone number", 
    "pw" => "password", 
    "pw2" => "password confirmation"];

// list of input field names that fail server side validation:
$is_invalid_list = [];

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
    <script src="script.js"></script>
</head>
<body>
    <div class="container mt-2 p-auto">
        <?php
            // Here we get form POST data and it should be checked  (server side validation) 

            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                // code validation functions here for all the input fields that need it

                function validateText($x) {
                    if (strlen($x) == 0)
                        return "empty field";
                    return "";
                }

                function validateEmail($x) {
                    // use built-in email validation function in php:
                    if (!filter_var($x, FILTER_VALIDATE_EMAIL))
                        return "invalid email address";
                    return "";
                }

                function validatePw($x) {
                    if (strlen($x) == 0)
                        return "password is empty";
                    if (!preg_match(PW_PATTERN, $x))
                        return "password is too weak";
                    return "";
                }

                function validatePw2($x) {
                    // pw2 has to match pw:
                    if ($x != recall("pw", false))
                        return "passwords do not match";
                    return "";
                }

                // obtain the POST data:
                $username = recall("username", false);
                $fullname = recall("fullname", false);
                $email = recall("email", false);
                $phone = recall("phone", false);
                $pw = recall("pw", false);
                $pw2 = recall("pw2", false);

                if (DEBUG_MODE) {     
                    // make an alert that displays the input values:
                    echo "<div class=\"alert alert-primary alert-dismissible\" role=\"alert\">";
                    echo "<div class=\"h5\">[DEBUG] Form input:</div>";
                    echo "Username: " . $username . "<br>";
                    echo "Full name: " . $fullname . "<br>";
                    echo "email: " . $email . "<br>";
                    echo "phone: " . $phone . "<br>";
                    echo "Password: " . $pw . "<br>";
                    echo "Password (confirmation): " . $pw2 . "<br>";
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                }

                
                // Server-side validation:

                // list of flaws found on the form:
                $flaws = [];

                $is_invalid_list = [];

                // attach a validation function to each input that needs validation:
                $validate_methods = ["username" => "validateText", 
                    "fullname" => "validateText", 
                    "email" => "validateEmail",
                    "phone" => "validateText",
                    "pw" => "validatePw",
                    "pw2" => "validatePw2"];
                // run the validation functions and keep track of problems 
                // in $flaws and $is_invalid_list
                foreach ($validate_methods as $name => $fn) {
                    $result = call_user_func($fn, recall($name, false));
                    if ($result != "") {
                        $flaws[] = "Invalid " . $field_names[$name] . ": " . $result;
                        $is_invalid_list[$name] = $result;
                    }
                }

                if (!$flaws) {
                    // add user to database here..

                    $stmt = "SELECT * FROM users WHERE username=?";
                    $result = substitute_and_execute($conn, $stmt, $username);
                    $count = $result['success'] ? mysqli_num_rows($result['value']) : 0;
                    if ($count == 0) {
                        $stmt = "INSERT INTO users (username, fullname, email, phone, pw_hash) VALUES (?, ?, ?, ?, ?)";
                        $hash = password_hash(PEPPER . $pw, PASSWORD_DEFAULT);
                        $result = substitute_and_execute($conn, $stmt, $username, $fullname, $email, $phone, $hash);
                        if ($result['success']) { 
                            // TODO session, redirect 
                        } else {
                            // insert failed
                            $flaws[] = "INSERT INTO failed: " . $result['value'];
                        }
                    } else {
                        $flaws[] = "username already exists";
                        $is_invalid_list["username"] = "username already exists";
                    }
                }
                if ($flaws) {
                    // found a flaw - report the found flaws to the user
                    echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">";
                    echo "<div class=\"h5\">ERROR:</div>";
                    if (count($flaws) == 1)
                        echo $flaws[0];
                    else {
                        foreach ($flaws as $flaw) 
                            echo "<li>$flaw</li>";
                    }
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                } else {
                    $_SESSION["username"] = $username;
                    header("Location: base.php");
                    exit;
                }
            }
        ?>

        <div class="row m-2">
            <h1>Registration <?php if (DEBUG_MODE) echo "[DEBUG MODE]"; ?></h1>
            <!-- form starts here -->
            <form id="my_form" class="needs-validation" novalidate method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <div class="row my-3">

                    <?php $field = "username"; ?>
                    <div class="col-sm-6">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="username" name="username" placeholder="Username" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="username-feedback" data-default="Valid username is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "fullname"; ?>
                    <div class="col-sm-6">
                        <label for="fullname" class="form-label">Full name:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="fullname" name="fullname" placeholder="Full name" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="fullname-feedback" data-default="Valid full name is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">

                    <?php $field = "email"; ?>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control <?php add_validation_class($field); ?>" id="email" name="email" placeholder="Enter email address" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="email-feedback" data-default="Valid email is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "phone"; ?>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label">Phone number:</label>
                        <input type="text" class="form-control <?php add_validation_class($field); ?>" id="phone" name="phone" placeholder="Phone number" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="phone-feedback" data-default="Valid phone number is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">

                    <?php $field = "pw"; ?>
                    <div class="col-sm-6">
                        <label for="pw" class="form-label">Password:</label>
                        <input type="password" class="form-control <?php add_validation_class($field); ?>" id="pw" name="pw" placeholder="Enter password" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="pw-feedback" data-default="Password is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "pw2"; ?>
                    <div class="col-sm-6">
                        <label for="pw2" class="form-label">Password confirmation:</label>
                        <input type="password" class="form-control <?php add_validation_class($field); ?>" id="pw2" name="pw2" placeholder="Retype your password" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="pw2-feedback" data-default="Confirm the password.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                </div>


                <!-- Submit -->
                <button type="submit" name="submit" id="submit" class="btn btn-primary btn-lg">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>