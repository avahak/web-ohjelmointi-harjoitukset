<?php

$db = "sakila";
require "../sql_connect.php";

// use this to output some extra info for debugging:
define('DEBUG_MODE', 0);

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

// Used to extract all possible values of ENUM or SET from a field
function extract_range($conn, $table, $field) {
    // cannot use substitution with table or field names so just use plain old:
    $stmt = "SHOW COLUMNS FROM $table WHERE Field='$field'";
    $result = substitute_and_execute($conn, $stmt);
    $range = [];
    if ($result['success']) { 
        $cleaner_pattern = '/\'([^\']*)\'/';   // matches content that starts and ends with '
        $row = $result['value']->fetch_assoc();
        $dirty_range = explode(",", $row['Type']);
        foreach ($dirty_range as $dirty) {
            if (preg_match($cleaner_pattern, $dirty, $matches)) 
                $range[] = $matches[1];  // [1] for only stuff inside first capture group ()
        }
    }
    return $range;
}

// associate a text description for each input field name:
$field_names = ["title" => "title", 
    "description" => "description", 
    "release_year" => "release year", 
    "language" => "language", 
    "rental_rate" => "rental rate", 
    "rental_duration" => "rental duration", 
    "cost" => "replacement cost", 
    "length" => "length", 
    "rating" => "rating", 
    "cb" => "special features"];

// list of input field names that fail server side validation:
$is_invalid_list = [];

// list of blacklisted words just to explore one possible way of text validation:
$word_blacklist = ["hitto", "harmi", "pahus", "pentele", "kurja"];

// extract $ratings:
$ratings = extract_range($conn, "film", "rating");

// extract $special_features:
$special_features = extract_range($conn, "film", "special_features");

// find languages and store it as $languages with elements language_id=>name:
$stmt = 'SELECT language_id, name FROM language';
$result = substitute_and_execute($conn, $stmt);
$languages = [];
if ($result['success']) { 
    foreach ($result['value'] as $row) 
        $languages[$row['language_id']] = $row['name'];
} else {
    echo "ERROR: " . $result['value'];
}
// print_r($ratings);
// echo "<br>";
// print_r($special_features);
// echo "<br>";
// print_r($languages);
// echo "<br>";
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add film</title>
    <!-- Lisää Bootstrap CSS-tiedosto -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <!-- Lisää oma CSS-tyylitiedosto -->
    <link href="styles.css" rel="stylesheet">
    <script src="script.js"></script>
</head>
<body>
    <div class="container mt-2 p-auto">
        <?php
            // Here we get form POST data and it should be checked  (server side validation) 
            // and if everything is ok we add the film to the database

            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                // code validation functions here for all the input fields that need it

                function validateText($x) {
                    global $word_blacklist;
                    foreach ($word_blacklist as $word) 
                        if (strpos(strtolower($x), $word) !== false)
                            return "inappropriate language";   // $x contains blacklisted word
                    if (strlen($x) == 0)
                        return "empty field";
                    return "";
                }

                function validateNumeric($x) {
                    if (!is_numeric($x))
                        return "not numeric";
                    if ($x >= 10000)
                        return "too high";
                    if ($x < 0)
                        return "negative value";
                    return "";
                }

                // custom validation function for year:
                function validateYear($x) {
                    if (!ctype_digit($x))
                        return "not numeric";
                    if ($x <= 1900)
                        return "too low";
                    if ($x >= 2100)
                        return "too high";
                    return "";
                }

                // obtain the POST data:
                $title = recall("title", false);
                $description = recall("description", false);
                $release_year = recall("release_year", false);
                $language = recall("language", false);
                $rental_rate = recall("rental_rate", false);
                $rental_duration = recall("rental_duration", false);
                $cost = recall("cost", false);
                $length = recall("length", false);
                $rating = recall("rating", false);
                $cb = isset($_POST["cb"]) ? $_POST["cb"] : [];  // cant use recall becuse empty is [], not ""

                // sf_string is a string that lists all selected special features
                $sf_string = "";
                foreach ($cb as $key => $value) 
                    $sf_string .= (strlen($sf_string) == 0 ? "" : ",") . $special_features[$value];

                if (DEBUG_MODE) {     
                    // make an alert that displays the input values:
                    echo "<div class=\"alert alert-primary alert-dismissible\" role=\"alert\">";
                    echo "<div class=\"h5\">[DEBUG] Form input:</div>";
                    echo "Title: " . $title . "<br>";
                    echo "Description: " . $description . "<br>";
                    echo "Release year: " . $release_year . "<br>";
                    echo "Language: " . $language . "<br>";
                    echo "Rental rate: " . $rental_rate . "<br>";
                    echo "Rental duration: " . $rental_duration . "<br>";
                    echo "Replacement cost: " . $cost . "<br>";
                    echo "Length: " . $length . "<br>";
                    echo "Rating: " . $rating . "<br>";
                    echo "Special features: |" . $sf_string . "|<br>";
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                }

                
                // Server-side validation:

                // list of flaws found on the form:
                $flaws = [];

                $is_invalid_list = [];

                // attach a validation function to each input that needs validation:
                $validate_methods = ["title" => "validateText", 
                    "description" => "validateText", 
                    "release_year" => "validateYear",
                    "language" => "validateNumeric",
                    "rental_rate" => "validateNumeric",
                    "rental_duration" => "validateNumeric",
                    "cost" => "validateNumeric",
                    "length" => "validateNumeric",
                    "rating" => "validateText"];
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
                    // test for duplicates (kinda arbitrary test but just for demo):
                    $stmt = "SELECT * FROM film WHERE title=? AND release_year=? AND language_id=?";
                    $result = substitute_and_execute($conn, $stmt, $title, $release_year, $language);
                    $count = $result['success'] ? mysqli_num_rows($result['value']) : 0;
                    if ($count == 0) {
                        // no film found: try to insert film into database:
                        $stmt = "INSERT INTO film (title, description, release_year, language_id, rental_rate, rental_duration, replacement_cost, length, rating, special_features) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $result = substitute_and_execute($conn, $stmt, $title, $description, $release_year, $language, $rental_rate, $rental_duration, $cost, $length, $rating, $sf_string);
                        if ($result['success']) { 
                            foreach (["title", "description", "release_year", "language", "rental_rate", "rental_duration", "cost", "length", "rating", "cb"] as $field)
                                unset($_POST[$field]);      // start the form fresh
                        } else {
                            // insert failed
                            $flaws[] = "INSERT INTO error: " . $result['value'];
                        }
                    } else {
                        $flaws[] = "Film already exists in the database.";
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
                    // no flaws - report on the insert success to the user
                    echo "<div class=\"alert alert-success alert-dismissible\" role=\"alert\">";
                    echo "<div class=\"h5\">Great success!</div>";
                    echo "Film inserted into database!";
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                }
            }
        ?>

        <div class="row m-2">
            <h1>Add new film <?php if (DEBUG_MODE) echo "[DEBUG MODE]"; ?></h1>
            <!-- form starts here -->
            <form id="film_form" class="needs-validation" novalidate method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <?php $field = "title"; ?>
                <div class="row mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control <?php add_validation_class($field); ?>" id="title" name="title" placeholder="Title of the film" required <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                    <div class="invalid-feedback" id="title-feedback" data-default="Valid title is required.">
                        <?php custom_feedback($field); ?>
                    </div>
                </div>

                <?php $field = "description"; ?>
                <div class="row mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control <?php add_validation_class($field); ?>" id="description" name="description" placeholder="Description" required rows="3"><?php echo recall($field, true); ?></textarea>
                    <div class="invalid-feedback" id="description-feedback" data-default="Valid description is required.">
                        <?php custom_feedback($field); ?>
                    </div>
                </div>
                <div class="row mb-3">

                    <?php $field = "release_year"; ?>
                    <div class="col-sm-6">
                        <label for="release_year" class="form-label">Release year:</label>
                        <input type="number" class="form-control <?php add_validation_class($field); ?>" id="release_year" name="release_year" placeholder="Release year" required min="1900" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="release_year-feedback" data-default="Valid release year is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "language"; ?>
                    <div class="col-sm-6">
                        <label for="language" class="form-label">Language:</label>
                        <select class="form-select selectpicker <?php add_validation_class($field); ?>" id="language" name="language" required>
                            <option value="" disabled selected>Select language</option>
                            <?php 
                                foreach ($languages as $key => $value) {
                                    $s_key = sanitize_for_html($key);
                                    $s_value = sanitize_for_html($value);
                                    echo "<option value='" . $s_key . "' " . ($key == recall($field, false) ? "selected" : "") . ">" . $s_value . "</option>";
                                }
                            ?>
                        </select>
                        <div class="invalid-feedback" id="language-feedback" data-default="Select a language.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">

                    <?php $field = "rental_rate"; ?>
                    <div class="col-sm-6">
                        <label for="rental_rate" class="form-label">Rental rate:</label>
                        <input type="number" step="0.01" class="form-control <?php add_validation_class($field); ?>" id="rental_rate" name="rental_rate" placeholder="Rental rate" required min="0" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="rental_rate-feedback" data-default="Valid rental rate is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>
                    
                    <?php $field = "rental_duration"; ?>
                    <div class="col-sm-6">
                        <label for="rental_duration" class="form-label">Rental duration:</label>
                        <input type="number" class="form-control <?php add_validation_class($field); ?>" id="rental_duration" name="rental_duration" placeholder="Rental duration" required min="0" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="rental_duration-feedback" data-default="Valid rental duration is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">

                    <?php $field = "cost"; ?>
                    <div class="col-sm-6">
                        <label for="cost" class="form-label">Replacement cost:</label>
                        <input type="number" step="0.01" class="form-control <?php add_validation_class($field); ?>" id="cost" name="cost" placeholder="Replacement cost" required min="0" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="cost-feedback" data-default="Valid replacement cost is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>
                    
                    <?php $field = "length"; ?>
                    <div class="col-sm-6">
                        <label for="length" class="form-label">Length:</label>
                        <input type="number" class="form-control <?php add_validation_class($field); ?>" id="length" name="length" placeholder="Length" required min="0" max="10000" <?php echo "value=\"" . recall($field, true) . "\"" ?>>
                        <div class="invalid-feedback" id="length-feedback" data-default="Valid length is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">

                    <?php $field = "rating"; ?>
                    <div class="col-12 col-sm-4">
                        <label for="rating" class="form-label">Rating:</label>
                        <select class="form-select selectpicker <?php add_validation_class($field); ?>" id="rating" name="rating" required>
                            <option value="" disabled selected>All ratings</option>
                            <?php 
                                foreach ($ratings as $key => $value) {
                                    $s_key = sanitize_for_html($key);
                                    $s_value = sanitize_for_html($value);
                                    echo "<option value='" . $s_key . "' " . ($key == recall($field, false) ? "selected" : "") . ">" . $s_value . "</option>";
                                }
                            ?>
                        </select>
                        <div class="invalid-feedback" id="rating-feedback" data-default="Valid rating is required.">
                            <?php custom_feedback($field); ?>
                        </div>
                    </div>

                    <?php $field = "cb"; ?>
                    <div class="col-12 col-sm-8">
                        <label class="form-label">Special features:</label>
                        <div class="row">
                            <?php
                                $k = 0;
                                foreach ($special_features as $sf) {
                                    $checked = (isset($_POST["cb"]) ? in_array($k, $_POST["cb"]) : false);
                                    echo '<div class="col-6 custom-control custom-checkbox">';
                                    echo "<input type=\"checkbox\" class=\"mx-2 custom-control-input\" name=\"cb[]\" value=\"$k\" id=\"cb$k\"" . ($checked ? "checked" : "") . ">";
                                    echo "<label class=\"custom-control-label checkbox-label\" for=\"cb$k\">$sf</label>";
                                    echo '</div>';
                                    $k++;
                                }
                            ?>
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


<?php 
$conn->close();
?>