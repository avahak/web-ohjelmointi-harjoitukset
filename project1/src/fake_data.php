<?php

require_once __DIR__ . "/config.php";

// FakeUser is a class for generating plausible data for an imaginary user.

define("NAMES_FILE", $GLOBALS["CONFIG"]["SITE"] . "/project1/resources/names.json");
define("WORDS_FILE", $GLOBALS["CONFIG"]["SITE"] . "/project1/resources/words.json");

class FakeUser {
    static $NAMES = null;
    static $WORDS = null;
    static $GENDERS = [["male", 45], ["female", 45], ["unknown", 10]];
    static $EMAIL_DOMAINS = [["gmail.com", 1000], ["yahoo.com", 500], ["outlook.com", 400], ["aol.com", 300], ["icloud.com", 200], ["mail.com", 150], ["protonmail.com", 150], ["yandex.com", 100], ["zoho.com", 100], ["outlook.co.uk", 100], ["qq.com", 50], ["163.com", 50], ["126.com", 50], ["rediffmail.com", 50], ["yahoo.co.uk", 50], ["gmx.com", 50], ["rocketmail.com", 50], ["aim.com", 50], ["comcast.net", 50], ["verizon.net", 50]];
    static $EMAIL_LOCAL_PART = [
        [["f", ".", "l"], 100],      // John.Smith
        [["l", ".", "f"], 90],       // Smith.John
        [["f", "r2-6"], 80],         // JohnK4s2x
        [["f", "y"], 70],           // John22
        [["y", "l"], 70],           // 22Smith
        [["f", "r2-6", ".", "y"], 60],  // JohnK4s2x.22
        [["f", ".", "l", "y"], 60],  // John.Smith22
        [["r4-8"], 20]              // K4s2x7a
    ];     
    static $CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    static $SPECIAL_CHARS = '!#%&/()=?.,<>*-+_';
    static $PASSWORDS = [
        [["w", "n3-4", "s"], 100],              // dog1234!
        [["s", "n3-4", "w"], 100],              // !1234dog
        [["W", "n2-2", "s"], 90],               // CAT12!
        [["s", "n2-2", "W"], 90],               // !12CAT
        [["n3-4", "r3-4s3-4"], 80],             // 1234!a(c#
        [["w", "r3-4s1-1"], 70],                // dogabc1!
        [["r4-4s2-2"], 70],                     // abcd#$
        [["W", "w", "n2-2", "r2-3s1-1"], 60],   // CATdog12abc!
        [["w", "n2-2", "r2-3s2-3"], 60],        // dog53g)ka.
        [["r4-8s3-4"], 20]                      // &g,!koa-g
    ];    
    public $firstname;
    public $lastname;
    public $birth_year;
    public $gender;
    public $email;
    public $password;

    function __construct() {
        FakeUser::load_files();

        $this->birth_year = rand(1923, 2005);

        $this->gender = FakeUser::weighted_sample(FakeUser::$GENDERS);

        if (($this->gender == "male") || ($this->gender == "other" && rand(0, 1) == 0))
            $this->firstname = FakeUser::weighted_sample(FakeUser::$NAMES["male_names"]);
        else
            $this->firstname = FakeUser::weighted_sample(FakeUser::$NAMES["female_names"]);

        $this->lastname = FakeUser::weighted_sample(FakeUser::$NAMES["surnames"]);

        $this->email = $this->generate_email();

        $this->password = $this->generate_password();
    }

    function to_string() {
        return get_class($this) . "(firstname=" . $this->firstname . ", lastname=" . $this->lastname . ", gender=" . $this->gender . ", birth_year=" . $this->birth_year . ", email=" . $this->email . ", password=" . htmlspecialchars($this->password) . ")";
    }

    // Returns value that user might fill on a form asking for "name".
    function fill_form_name() {
        $name_rand = rand(0, 3);
        $name = $this->firstname;
        if ($name_rand == 1) 
            $name = $this->lastname;
        if ($name_rand == 2) 
            $name = $this->firstname . " " . substr($this->lastname, 0, 1);
        if ($name_rand == 3) 
            $name = $this->firstname . " " . $this->lastname;
        return $name;
    }

    // Generates a random email local part based on the rule given.
    private function generate_email_local_part($rule) {
        $local_part = "";
        foreach ($rule as $component) {
            if ($component == "f") {
                $local_part .= $this->firstname;
            } else if ($component == "l") {
                $local_part .= $this->lastname;
            } else if (preg_match('/^r(\d+)-(\d+)$/', $component, $matches)) {
                // Handle "ra-b" notation for generating a random string
                $len = rand($matches[1], $matches[2]);
                $local_part .= FakeUser::generate_random_string($len);
            } else if ($component == "y") {
                $local_part .= substr($this->birth_year, 2);
            } else if ($component == ".") {
                $local_part .= ".";
            } else {
                exit("ERROR in generate_email_local_part()!");
            }
        }
        return $local_part;
    }

    function generate_email() {
        $rule = FakeUser::weighted_sample(FakeUser::$EMAIL_LOCAL_PART);
        $local_part = $this->generate_email_local_part($rule);
        $domain = FakeUser::weighted_sample(FakeUser::$EMAIL_DOMAINS);
        return $local_part . "@" . $domain;
    }

    private function generate_password() {
        $rule = FakeUser::weighted_sample(FakeUser::$PASSWORDS);
        $password = "";
        foreach ($rule as $component) {
            if ($component == "w") {
                $password .= FakeUser::sample(FakeUser::$WORDS);
            } else if ($component == "W") {
                $password .= strtoupper(FakeUser::sample(FakeUser::$WORDS));
            }
            else if (preg_match('/^n(\d+)-(\d+)$/', $component, $matches)) {
                // Generate a random number with the specified number of digits
                $num_digits = rand($matches[1], $matches[2]);
                $password .= sprintf("%0" . $num_digits . "d", rand(0, 10**$num_digits-1));
            } else if (preg_match('/^r(\d+)-(\d+)s(\d+)-(\d+)$/', $component, $matches)) {
                // Generate a string with a specified number of regular characters and special characters
                $num_regular = rand($matches[1], $matches[2]);
                $num_special = rand($matches[3], $matches[4]);
                $password .= FakeUser::generate_random_string($num_regular, $num_special);
            } else if ($component == "s") {
                $password .= FakeUser::generate_random_string(0, 1);
            } else {
                exit("ERROR in generate_password()!");
            }
        }
        return $password;
    }

    // Creates a random string containing $num_regular regular characters and
    // $num_special special characters.
    static function generate_random_string($num_regular, $num_special=0) {
        $s = "";
        for ($k = 0; $k < $num_regular; $k++) 
            $s .= FakeUser::$CHARS[rand(0, strlen(FakeUser::$CHARS)-1)];
        for ($k = 0; $k < $num_special; $k++) 
            $s .= FakeUser::$SPECIAL_CHARS[rand(0, strlen(FakeUser::$SPECIAL_CHARS)-1)];
        return str_shuffle($s);
    }

    // Picks a random row from the first col of $table.
    static function sample($table) {
        $num = count($table);
        return $table[rand(0, $num-1)][0];
    }

    // Picks a random row from the first col of $table based on weights in the second col.
    // NOTE: slow for large arrays! Could be optimized by using a binary tree.
    static function weighted_sample($table) {
        $y_sum = 0.0;
        foreach ($table as $key => [$x, $y])
            $y_sum += (float)$y;
        $y_target = rand()/getrandmax() * $y_sum;
        $y_sum = 0.0;
        foreach ($table as $key => [$x, $y]) {
            $y_sum += (float)$y;
            if ($y_sum >= $y_target)
                return $x;
        }
    }

    // Loads names.json, loading is done only once.
    static function load_files() {
        if (!FakeUser::$NAMES) {
            FakeUser::$NAMES = json_decode(file_get_contents(NAMES_FILE), true);
            if (!FakeUser::$NAMES)
                exit("Unable to load names json.");
        }
        if (!FakeUser::$WORDS) {
            FakeUser::$WORDS = json_decode(file_get_contents(WORDS_FILE), true);
            if (!FakeUser::$WORDS)
                exit("Unable to load words json.");
        }
    }
}

// function old_create_names_json() {
//     function load($file) {
//         if (!file_exists($file))
//             return [];
//         $list = [];
//         $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//         return $lines;
//     }
//     $male_names = load("d:/projects/project1/male_names.txt");
//     $female_names = load("d:/projects/project1/female_names.txt");
//     $surnames = load("d:/projects/project1/surnames.txt");
//     $table = json_encode(["male_names" => $male_names, "female_names" => $female_names, "surnames" => $surnames],  JSON_PRETTY_PRINT);
//     $json_file = fopen("d:/projects/project1/names.json", "w") or exit("Unable to write file.");
//     fwrite($json_file, $table);
//     fclose($json_file);
// }

// Reads a csv file and returns it as an array.
function read_csv($path) {
    $handle = fopen($path, "r");
    $data = [];
    while (($row = fgetcsv($handle)) !== false) {
        $row[1] = str_replace(",", "", $row[1]);    // Ex. removes comma from 279,049
        $data[] = $row;
    }
    return $data;
}

// Creates names.json from csv files that are based on excel data from Väestökeskus.
function create_names_json() {
    $male_names = read_csv("d:/resources/words/male_names.csv");
    $female_names = read_csv("d:/resources/words/female_names.csv");
    $surnames = read_csv("d:/resources/words/surnames.csv");
    $table = json_encode(["male_names" => $male_names, "female_names" => $female_names, "surnames" => $surnames],  JSON_PRETTY_PRINT);
    $json_file = fopen(NAMES_FILE, "w") or exit("Unable to write file.");
    fwrite($json_file, $table);
    fclose($json_file);
}

// Creates words.json from a csv file based on data from www.wordfrequency.info.
function create_words_json() {
    $table = json_encode(read_csv("d:/resources/words/word_frequency.csv"), true);
    $json_file = fopen(WORDS_FILE, "w") or exit("Unable to write file.");
    fwrite($json_file, $table);
    fclose($json_file);
}

$is_direct_request = str_ends_with(str_replace('\\', '/', __FILE__), $_SERVER['SCRIPT_NAME']);
if ($is_direct_request) {
    for ($k = 1; $k <= 30; $k++) {
        $fu = new FakeUser();
        echo "<br>$k: " . $fu->to_string();
    }
}

?>