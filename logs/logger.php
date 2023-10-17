<?php

date_default_timezone_set('Europe/Helsinki');
define("LOG_FILTER_OPTIONS", ["ALL", "DEBUG", "INFO", "WARNING", "ERROR"]);

// log entries: key1=value1|key2=value2|...|key_n=value_n
// keys must include level,time,logger_msg

class Logger {
    private static $char_encode = ["%" => "%25", "\n" => "%0A", "|" => "%7C", "=" => "%3D"];
    protected $path;

    function __construct($path=__DIR__ . "/" . "log.txt") {
        $this->path = $path;

        // If the log file is too big (1mb), trim it down (to 20%):
        if (file_exists($this->path) && filesize($this->path) > 1 * 1024 * 1024) {
            $lines = file($this->path);
            $new_num_lines = ceil(0.2 * count($lines));
            $new_lines = array_slice($lines, -$new_num_lines);
            file_put_contents($this->path, implode('', $new_lines));
        }
    }

    public static function escape_string($input) {
        return strtr($input, self::$char_encode);
    }

    public static function unescape_string($input) {
        return strtr($input, array_flip(self::$char_encode));
    }

    // Function to log a message to a log file
    private function log_entry($msg, $level, $properties) {
        $append = function($s, $key, $value, $add_pipe) {
            $pipe = $add_pipe ? "|" : "";
            return $s . $pipe . Logger::escape_string($key) . "=" . Logger::escape_string($value);
        };
        $entry = $append("", "level", $level, false);
        $entry = $append($entry, "time", date('Y-m-d H:i:s'), true);
        if ($msg)
            $entry = $append($entry, "logger_msg", $msg, true);
        foreach ($properties as $key => $value) 
            $entry = $append($entry, $key, $value, true);

        // call backtrace:
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);
        $trace_string = "";
        for ($k = 1; $k < count($trace); $k++) {
            $caller = $trace[$k];
            $caller_file = pathinfo($caller['file'], PATHINFO_FILENAME);
            $caller_line = $caller['line'];
            $caller_function = $caller['function'];
            $trace_string .=  ($trace_string ? "\n" : "\t") . $caller_file . "($caller_line)";
            if ($k > 1)
                $trace_string .= ": " . $caller_function;
        }
        $entry = $append($entry, "backtrace", $trace_string, true);

        // Open the log file (create if it doesn't exist) in append mode
        if ($handle = fopen($this->path, 'a')) {
            fwrite($handle, $entry . PHP_EOL);
            fclose($handle);
        } else {
            echo 'Unable to open log file for writing.';
        }
    }

    function debug($msg, $properties=[]) {
        $this->log_entry($msg, "DEBUG", $properties);
    }

    function info($msg, $properties=[]) {
        $this->log_entry($msg, "INFO", $properties);
    }

    function warning($msg, $properties=[]) {
        $this->log_entry($msg, "WARNING", $properties);
    }

    function error($msg, $properties=[]) {
        $this->log_entry($msg, "ERROR", $properties);
    }
}

// Reads the log file, parses it, and returns the most recent entries as an array.
function read_entries($file) {
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
            // every entry needs to have level and it needs to be in LOG_FILTER_OPTIONS
            if (!array_key_exists("level", $properties) || !in_array($properties["level"], LOG_FILTER_OPTIONS)) 
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

// Returns a string that tells the time difference between now and $time.
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


// Forms a table with the log entries.
function create_log_table($entries) {
    ob_start();
    ?>
    <table class="table table-dark table-bordered" style="border-collapse:separate;border-spacing:0 10px;">
        <thead>
            <tr style="position:sticky;top:0px;">
                <th class="bg-secondary" style="width:10%;">Level</th>
                <th class="bg-secondary" style="width:15%;">Time</th>
                <th class="bg-secondary" style="width:20%;">Message</th>
                <th class="bg-secondary" style="width:55%;">Other</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <?php 
                $time_diff = get_time_diff($entry["time"]);
                $arr1 = ["DEBUG" => "bg-dark", "INFO" => "bg-primary", "WARNING" => "bg-warning", "ERROR" => "bg-danger"];
                $arr2 = ["DEBUG" => "table-dark", "INFO" => "table-primary", "WARNING" => "table-warning", "ERROR" => "table-danger"];
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

    <?php ob_end_flush();
}

?>