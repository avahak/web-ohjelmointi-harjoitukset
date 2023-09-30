<?php

require_once "./validation/validation_php.php";
require_once "./validation/template_inputs.php";

// Custom validation for cases that are not covered by the json file:
function my_custom_validation() {
    // echo "</br>Performing custom validation:";

    if (($_POST["select"] ?? "") == "Cat")
        invalidate("select", "Sorry, the cat was taken by another user!");

    if (isset($_POST["checkbox"]))
        if (in_array("Kitten", $_POST["checkbox"]) && in_array("Sword", $_POST["checkbox"]))
            invalidate("checkbox", "The kitten could be harmed by the sword if you carry both at the same time! Choose again.");
}

// Code that is executed on form submit if it passes all validation:
function my_validation_pass() {
    echo "<h2></br>Form passed automatic validation and custom validation.";
    echo "</br>Here user could be informed of success or redirected to some other page.</h2>";
    exit();
}

// Initialize the php script:
init_validation("./generic_form.json");
// Validate the form (this does nothing if there is no data in POST):
validate("my_custom_validation", "my_validation_pass");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Form</title>

    <!-- Bootstrap: -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <!-- The following includes javascript validation code: -->
    <?php include_validation_js("./validation/"); ?>
    
    <style> .form-label { font-weight: bold; } </style>
</head>

<body class="bg-secondary text-light">

<div class="container mt-3 p-3 bg-light text-dark" style="max-width:600px;">
    <form id="generic_form" class="needs-validation <?php echo ($_SERVER["REQUEST_METHOD"] == "POST" ? "was-validated" : ""); ?>" novalidate method="POST">
        <h2>Generic Form</h2>

        <input type="hidden" id="hidden" name="hidden" value="hidden_value">

        <div class="mt-3">
            <?php template_input("text", "name", "Enter your First Name", "First Name", "col-12 col-sm-4", "col-12 col-sm-8"); ?>
        </div>

        <div class="mt-3">
            <?php template_input("email", "email", "Email Input", "Email Placeholder", "col-12 col-sm-4", "col-12 col-sm-8"); ?>
        </div>
        
        <div class="row mt-3">
            <div class="col-sm-6">
                <?php template_input("password", "pw", "Password Input", "Password Placeholder", "col-12", "col-12"); ?>
            </div>
            <div class="col-sm-6">
                <?php template_input("password", "pw2", "Password (Confirmation)", "Password Placeholder", "col-12", "col-12"); ?>
            </div>
        </div>

        <div class="row mt-3">
            <?php template_radio("radio", "Pick a Color", ["Red", "Blue", "Green"], true); ?>
        </div>

        <div class="mt-3">
            <?php template_select("select", "Select a Pet", ["None", "Cat", "Dog", "Rabbit"], [0], 0); ?>
        </div>

        <div class="mt-3">
            <?php template_checkbox("checkbox", "Choose Your Weapon", ["Sword", "Pen", "Axe", "Kitten", "Mace", "Banana Peel"], true); ?>
        </div>

        <div class="mt-3">
            <?php template_checkbox("agreeCheckbox", "", ["I agree to the Terms and Services"], true); ?>
        </div>

        <!-- <?php template_file("file", "Upload a Photo", "photo"); ?> // TODO NOT IMPLEMENTED -->

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>

    </form>
    <div class="mt-3">
        <a href="about:blank">A Link</a>
    </div>
    <!-- <?php create_alert(false); ?> -->

</div>

</body>
</html>
