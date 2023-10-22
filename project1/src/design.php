<?php 

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/user_operations.php";

init();

shared_script_start("Login");
?>

<script type="importmap">
{
    "imports": {
        "three": "../../node_modules/three/build/three.module.js",
        "three/addons/": "../../node_modules/three/examples/jsm/"
    }
}
</script>

<script type="module" src="./design.js"></script>

<div class="container d-flex bg-dark" style="width:100%;justify-content:center;">
    <div id="test"></div>
</div>

<?php shared_script_end(); ?>