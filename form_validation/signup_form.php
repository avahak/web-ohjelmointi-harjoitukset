<?php

require_once "./validation/validation_php.php";
require_once "./validation/template_inputs.php";

// Custom validation for cases that are not covered by the json file:
function my_custom_validation() {
    // Fail half of emails adresses:
    if (ord(md5($_POST["email"] ?? "")[0])%2 == 0) {
        invalidate("email", "Sorry, that email is already in use.");
    }
}

// Code that is executed on form submit if it passes all validation:
function my_validation_pass() {
    echo "</br><h2>Form passed all validation!</h2>";
    exit();
}

// Initialize the php script:
init_validation("./signup_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("my_custom_validation", "my_validation_pass");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>

    <!-- Bootstrap: -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <!-- The following includes javascript validation code: -->
    <?php include_validation_js("./validation/"); ?>
    
    <style> .form-label { font-weight: bold; } </style>
</head>

<body class="bg-secondary text-light">

<div class="container mt-3 p-3 bg-light text-dark" style="max-width:600px;">
    <form id="signup_form" class="needs-validation <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" ? "was-validated" : ""); ?>" novalidate method="POST">
        <h2>Sign up</h2>

        <div class="mt-4">
            <?php template_input("email", "email", "Email", "Email Placeholder", "col-12 col-sm-3", "col-12 col-sm-9"); ?>
        </div>
        
        <div class="row mt-3">
            <div class="col-sm-6">
                <?php template_input("password", "pw", "Password", "Password Placeholder", "col-12", "col-12"); ?>
            </div>
            <div class="col-sm-6">
                <?php template_input("password", "pw2", "Password (Confirmation)", "Password Placeholder", "col-12", "col-12"); ?>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>

    </form>
    <div class="my-3">
        <a href="about:blank">Back to Blank Page</a>
    </div>

    <?php create_alert(false); ?>

</div>

</body>
</html>
