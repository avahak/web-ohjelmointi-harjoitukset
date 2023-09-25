<?php

require_once "logger.php";

$filter_options = ["ALL", "DEBUG", "INFO", "WARNING", "ERROR"];

function read_entries($file) {
    global $filter_options;
    $entries = [];
    $file_extension = pathinfo($file, PATHINFO_EXTENSION);
    if (file_exists($file) && in_array($file_extension, ["txt", "log"])) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_reverse($lines) as $line) {
            $parts = explode("|", $line);
            if (count($parts) < 3)
                exit("Invalid log file.");
            $properties = [];
            foreach ($parts as $part) {
                $key_value = explode("=", $part);
                if (count($key_value) != 2)
                    exit("Invalid log file.");
                $html_key = htmlspecialchars(Logger::unescape_string($key_value[0]));
                $html_value = htmlspecialchars(Logger::unescape_string($key_value[1]));
                $properties[$html_key] = $html_value;
            }
            // every entry needs to have level and it needs to be in $filter_options
            if (!array_key_exists("level", $properties) || !in_array($properties["level"], $filter_options)) 
                exit("Invalid log file (no level).");
            // every entry needs to have time
            if ((!array_key_exists("time", $properties) || !$properties["time"]))
                exit("Invalid log file (no time).");
            $entries[] = $properties;
            if (count($entries) >= 100)
                break;
        }
    }
    return $entries;
}

function get_time_diff($time) {
    $diff = ((new Datetime('now'))->diff(new Datetime($time)));
    if ($diff->days)
        return $diff->days . "d " . $diff->h . "h ago";
    elseif ($diff->h)
        return $diff->h . "h " . $diff->i . "m ago";
    elseif ($diff->i)
        return $diff->i . "m " . $diff->s . "s ago";
    elseif ($diff->s)
        return $diff->s . "s ago";
    return "now";
}

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
                    foreach ($filter_options as $option) {
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

    <table class="table table-dark mt-5 table-bordered" style="border-collapse:separate;border-spacing:0 10px;">
        <thead>
            <tr>
                <th style="width:10%;">Level</th>
                <th style="width:15%;">Time</th>
                <th style="width:20%;">Message</th>
                <th style="width:55%;">Other</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($log_entries as $entry): ?>
                <?php 
                $time_diff = get_time_diff($entry["time"]);
                $arr1 = ["DEBUG" => "bg-dark", "INFO" => "bg-dark", "WARNING" => "bg-warning", "ERROR" => "bg-danger"];
                $arr2 = ["DEBUG" => "table-dark", "INFO" => "table-dark", "WARNING" => "table-warning", "ERROR" => "table-danger"];
                $bg1 = $arr1[$entry["level"]];
                $bg2 = $arr2[$entry["level"]];
                ?>
                <tr class="log-entry" data-log-level="<?= $entry["level"] ?>">
                    <td class="<?= $bg1 ?> h5"><?= $entry["level"] ?></td>
                    <td class="<?= $bg2 ?>"><?= $entry["time"] . "</br>" . $time_diff ?></td>
                    <td class="<?= $bg2 ?>"><?= nl2br($entry["logger_msg"]) ?></td>
                    <td class="<?= $bg2 ?>">
                    <?php 
                    foreach ($entry as $key => $value) {
                        if (in_array($key, ["level", "time", "logger_msg"])) 
                            continue;
                        echo "<b>" . nl2br($key) . ": </b>" . nl2br($value) . "</br>";
                    }
                    ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
