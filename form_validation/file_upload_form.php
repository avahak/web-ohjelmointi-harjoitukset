<?php

require_once __DIR__ . "/validation/validation_php.php";
require_once __DIR__ . "/validation/template_inputs.php";

// Custom validation for cases that are not covered by the json file:
function my_custom_validation() {
    if (strlen($_POST["username"]) < 8)
        invalidate("username", "Username is short - rejected for testing.");
}

// Code that is executed on form submit if it passes all validation:
function my_validation_pass() {
    echo "<html><body>";
    echo "<h1></br>Form passed validation with JSON rules and custom validation.</h1><h3>";

    if (isset($GLOBALS["form_validation_temporary_files"]["image"])) {
        echo "</br>Here is the uploaded image:";
        $image_temp_file = $GLOBALS["form_validation_temporary_files"]["image"]["temp_file"];
        $image_content_base64 = base64_encode(file_get_contents($image_temp_file));
        echo "</br><img src=\"data:image/png;base64,$image_content_base64\" style=\"max-width:200px;max-height:200px;width:auto;height:auto;\">";
        $image_temp_file = $GLOBALS["form_validation_temporary_files"]["image"]["temp_file"];
        delete_file_if_exists($image_temp_file);
    }

    if (isset($GLOBALS["form_validation_temporary_files"]["document"])) {
        $document_temp_file = $GLOBALS["form_validation_temporary_files"]["document"]["temp_file"];
        $document_original = $GLOBALS["form_validation_temporary_files"]["document"]["original"];
        echo "</br>The uploaded document was: $document_original";
        delete_file_if_exists($document_temp_file);
    }

    echo "<br><a href=".">Back</a>";

    echo "</h3></body></html>";
    exit();
}

// Initialize the php script:
init_validation("./file_upload_form.json", $_SERVER['DOCUMENT_ROOT'] . "/my_temporary_files/");
// Validate the form (this does nothing if there is no data in POST):
validate("my_custom_validation", "my_validation_pass");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Form</title>

    <!-- Bootstrap: -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <!-- The following includes javascript validation code: -->
    <?php include_validation_js("./validation/"); ?>
    
    <style> .form-label { font-weight: bold; } </style>
</head>

<body class="bg-secondary text-light">

<div class="container mt-3 p-3 bg-light text-dark" style="max-width:600px;">
    <form id="file_upload_form" class="needs-validation" enctype="multipart/form-data" novalidate method="POST">
        <h2>File Upload Form</h2>

        <input type="hidden" id="hidden" name="hidden" value="hidden_value">

        <div class="mt-3">
            <?php template_input("text", "username", "Enter Username", "Username", "col-12 col-sm-4", "col-12 col-sm-8"); ?>
        </div>

        <div class="mt-3">
            <?php template_file_upload("image", "Upload Profile Picture"); ?>
            <?php template_file_upload_image_preview("image", "Image Preview", 200); ?>
        </div>

        <div class="mt-3">
            <?php template_file_upload("document", "Upload a Document (pdf or txt)"); ?>
        </div>

        <div class="mt-3">
            <?php template_checkbox("agreeCheckbox", "", ["I agree to the Terms and Services"], true); ?>
        </div>

        <div class="mt-3">
            <button type="submit" id="submit_button" class="btn btn-primary">Submit</button>
        </div>

    </form>

    <div class="my-3">
        <a href="generic_form.php">Generic Form</a><br>
        <a href="signup_form.php">Signup Form</a>
    </div>

    <?php 
    create_alert(); 
    create_debug_alert(); 
    ?>

</div>

</body>
</html>
