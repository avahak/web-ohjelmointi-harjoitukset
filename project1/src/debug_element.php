<?php

// Azure: log stream (logging), wwwroot (file view) 

// Adds debug info div:
function include_debug_div() {
    $GLOBALS["debug"] = ["how to use this?", "we already have g_logger"];
    ob_start(); ?>

    <div id="debug-div" class="d-block m-3" style="position:fixed;bottom:0;">
        <nav class="navbar navbar-dark bg-dark p-1">
            <ul class="nav nav-tabs navbar-dark bg-dark collapse debug-collapse" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" id="button-general" data-bs-toggle="tab" data-bs-target="#general" role="tab">General</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" id="button-debug" data-bs-toggle="tab" data-bs-target="#debug" role="tab">Debug</button>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".debug-collapse" style="outline:none;box-shadow:none;">
                <span class="navbar-toggler-icon"></span>
            </button>
        </nav>
        <div id="debug_content" class="collapse debug-collapse" style="overflow:auto; max-height:500px; max-width:400px; border:1px solid #ccc; padding:10px; background-color:#111;">
            <div id="general" class="tab-pane fade show active" role="tabpanel">
                <p><?php echo "SCRIPT_NAME: " . basename($_SERVER["SCRIPT_NAME"]); ?><br>
                <?php echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]; ?></p>
                <p><?php echo "_GET: " . var_export($_GET, true); ?></p>
                <p><?php echo "_POST: " . var_export($_POST, true); ?></p>
                <p><?php echo "_SESSION: " . var_export($_SESSION, true); ?></p>
                <p><?php echo "_COOKIE: " . var_export($_COOKIE, true); ?></p>
                <p><?php echo "_ENV: " . var_export($_ENV, true); ?></p>
            </div>
            <div id="debug" class="tab-pane fade" role="tabpanel" style="overflow-y:scroll;">
                <?php
                foreach (($GLOBALS["debug"] ?? []) as $value) {
                    echo "$value<br>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Make button toggle debug_div visibility:
        const debugDiv = document.getElementById("debug_div");
        function restoreDebugDivState() {
            const isVisible = (sessionStorage.getItem("debug_div_visible") === "true");
            if (isVisible)
                debugDiv.classList.remove("d-none");
            else 
                debugDiv.classList.add("d-none");
        }
        function toggleDebugDiv() {
            debugDiv.classList.toggle("d-none");
            const isVisible = !debugDiv.classList.contains("d-none");
            sessionStorage.setItem("debug_div_visible", isVisible);
        }
        function clearDebugDiv() {
            debugDiv.innerHTML = "";
        }
        // document.getElementById("debug_div_toggle").addEventListener("click", toggleDebugDiv);
        // document.getElementById("debug_div_clear").addEventListener("click", clearDebugDiv);
        // document.addEventListener("DOMContentLoaded", restoreDebugDivState);
    </script>

    <?php ob_end_flush();
}

?>