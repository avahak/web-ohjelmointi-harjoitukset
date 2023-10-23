<?php 

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/tokens.php";

$initial_values = null;

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["value"])) 
        $initial_values = @file_get_contents("../user_data/shots/" . $_GET["value"] . ".json", true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $php_input = file_get_contents("php://input");
    $file_name = random_string(12);
    file_put_contents("../user_data/shots/" . $file_name . ".json", $php_input);
    $query = "INSERT INTO shots (file_name) VALUES (?)";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $file_name);
    echo json_encode(["result" => $result, "message" => $file_name]);
    exit();
}

init();

shared_script_start("Design");
?>

<script type="importmap">
{
    "imports": {
        "three": "../../node_modules/three/build/three.module.js",
        "three/addons/": "../../node_modules/three/examples/jsm/"
    }
}
</script>

<script>
const INITIAL_VALUES = <?php echo ($initial_values ? $initial_values : "undefined"); ?>;
</script>

<script defer type="module" src="./design.js"></script>

<div class="container d-flex" style="width:100%;justify-content:center;">
    <div style="position:relative;">
        <div id="three-box"></div>
        <button class="btn btn-primary" id="save" style="position:absolute;top:20px;left:20px;">Save</button>
    </div>
</div>

<?php shared_script_end(); ?>