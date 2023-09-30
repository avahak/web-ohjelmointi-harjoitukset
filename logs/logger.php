<?php

date_default_timezone_set('Europe/Helsinki');

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

?>