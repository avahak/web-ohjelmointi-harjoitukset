<?php

$db = "sakila";
require "../sql_connect.php";

// find $genres:
$stmt = 'SELECT category_id, name FROM category ORDER BY name';
$result = substitute_and_execute($conn, $stmt);
$genres = [];
if ($result['success']) { 
    foreach ($result['value'] as $row) 
        $genres[$row['category_id']] = $row['name'];
} else {
    echo "ERROR: " . $result['value'];
}
// print_r($genres);

// find $movies:
$movies = [];
if (isset($_POST["name"]) && isset($_POST["genre"])) {
    $name = $_POST["name"];
    $genre = $_POST["genre"];
    // echo "Name: $name, genre: $genre<br>";

    // "AND title LIKE ? AND fc.category_id=?"
    $stmt = 'SELECT title, name, description, release_year, length, rating FROM film f, film_category fc, category c WHERE f.film_id=fc.film_id AND fc.category_id=c.category_id';
    $result = null;
    $params = [$conn, null];
    if ($genre != "all") {
        $stmt .= " AND fc.category_id=?";
        $params[] = $genre;
    }
    if ($name) {
        $stmt .= " AND title LIKE ?";
        $params[] = "%$name%";
    }
    $stmt .= " ORDER BY title";
    $params[1] = $stmt;
    $result = call_user_func_array("substitute_and_execute", $params);

    if ($result['success']) { 
        foreach ($result['value'] as $row) 
            $movies[] = [$row['title'], $row['name'], $row['description'], $row['release_year'], $row['length'], $row['rating']];
        // echo "Results: " . count($movies) . "<br>";
    } else {
        echo "ERROR: " . $result['value'];
    }
    // print_r(array_slice($movies, 1, 1));
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Database</title>
    <!-- Add Bootstrap links here -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</head>
<body class="bg-dark text-white">
    <div class="container">
        <div class="row m-4">
            <h1 class="mt-4">Movie Database</h1>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="row">
                    <div class="col">
                        <label for="name">Movie Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter movie name" 
                            <?php 
                            if (isset($_POST['genre'])) {
                                echo " value=\"" . sanitize_for_html($_POST['name']) . "\"";
                            }
                            ?>
                            >
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <label for="genreDropdown">Genre</label>
                        <select class="form-control" name="genre">
                            <option value="all">All genres</option>
                            <?php 
                                foreach ($genres as $key => $value) {
                                    $s = "";
                                    if (isset($_POST["genre"]) && ($_POST["genre"] == $key))
                                        $s = " selected";
                                    echo "<option value='" . sanitize_for_html($key) . "'$s>" . sanitize_for_html($value) . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col mt-3 d-flex justify-content-end align-items-end">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row m-4">
            <h1 class="my-4">Results: <?php echo count($movies); ?></h1>
            <!-- Movie List -->
            <?php if ($movies) { ?>
                <table class="table bg-dark text-white">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Year</th>
                            <th>Length</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        foreach ($movies as $key => $value) {
                            echo "<tr>";
                            echo "<td>" . ($key+1) . "</td>";
                            echo "<td>" . sanitize_for_html($value[0]) . "</td>";
                            echo "<td>" . sanitize_for_html($value[1]) . "</td>";
                            echo "<td>" . sanitize_for_html($value[2]) . "</td>";
                            echo "<td>" . sanitize_for_html($value[3]) . "</td>";
                            echo "<td>" . sanitize_for_html($value[4]) . "</td>";
                            echo "<td>" . sanitize_for_html($value[4]) . "</td>";
                            echo "</tr>";
                        }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</body>
</html>