<?php 

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/user_operations.php";

init();

shared_script_start("Login");
?>

<!-- <script type="importmap">
  {
    "imports": {
      "three": "https://unpkg.com/three@0.157.0/build/three.module.js",
      "three/addons/": "https://unpkg.com/three@0.157.0/examples/jsm/"
    }
  }
</script> -->
<script type="importmap">
    {
        "imports": {
            "three": "../../node_modules/three/build/three.module.js",
            "three/addons/": "../../node_modules/three/examples/jsm/"
        }
    }
</script>

<script type="module" src="./design.js"></script>

<div class="container">
    Text1..
    <div id="test"></div>
    Text2..
</div>

<?php shared_script_end(); ?>