<?php

require_once __DIR__ . "/logger.php";

$log_file = "log.txt";
if (isset($_FILES["choose_file"]["name"]) && ($_FILES["choose_file"]["name"]))
    $log_file = $_FILES["choose_file"]["name"];
$log_entries = read_entries($log_file);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</head>
<body class="text-light" style="background-color:#345;">
<!-- <body> -->
    <div class="container mt-5">
        <div class="h1 my-5">Log Viewer</div>

        <form class="row d-flex justify-content-between" method="POST" enctype="multipart/form-data">
            <div class="col-sm-3">
                <div>
                    <label for="filter" class="">Filter:</label>
                    <select id="filter" name="filter" class="form-control">
                    <?php
                    foreach (LOG_FILTER_OPTIONS as $option) {
                        $selected = (isset($_POST['filter']) && ($_POST['filter'] === $option)) ? "selected" : "";
                        echo "<option value=\"$option\" $selected>$option</option>";
                    }
                    ?>
                    </select>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="row">
                    <label for="choose_file">Log file: <?= htmlspecialchars($log_file) ?></label>
                    <input type="file" id="choose_file" name="choose_file" class="my-1" onchange="this.form.submit()">
                </div>
            </div>
        </form>

    <div class="mt-5">
        <?php create_log_table($log_entries); ?>
    </div>

</div>

</body>
</html>