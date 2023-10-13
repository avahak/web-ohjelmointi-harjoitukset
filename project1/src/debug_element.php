<?php

// Azure: log stream (logging), wwwroot (file view) 

// Adds debug info div:
function include_debug_div() {
    ob_start(); ?>

    <div class="d-block m-2" style="position:fixed;bottom:0;">
        <button id="debug_div_clear" class="btn btn-sm btn-primary d-none">Clear</button>
        <button id="debug_div_toggle" class="btn btn-sm btn-secondary">General</button>
        <button class="btn btn-sm btn-secondary">Debug (add custom msgs/vars here!)</button>
        <button class="btn btn-sm btn-secondary">Close</button>
        <div id="debug_div" style="overflow:auto; max-height:500px; max-width:400px; border:1px solid #ccc; padding:10px; background-color:#111;">
            <p><?php echo "SCRIPT_NAME: " . basename($_SERVER["SCRIPT_NAME"]); ?><br>
            <?php echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]; ?></p>
            <p><?php echo "_GET: " . var_export($_GET, true); ?></p>
            <p><?php echo "_POST: " . var_export($_POST, true); ?></p>
            <p><?php echo "_SESSION: " . var_export($_SESSION, true); ?></p>
            <p><?php echo "_COOKIE: " . var_export($_COOKIE, true); ?></p>
            <p><?php echo "GLOBALS[\"debug\"]: " . var_export($GLOBALS["debug"] ?? null, true); ?></p>
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
        document.getElementById("debug_div_toggle").addEventListener("click", toggleDebugDiv);
        document.getElementById("debug_div_clear").addEventListener("click", clearDebugDiv);
        document.addEventListener("DOMContentLoaded", restoreDebugDivState);
    </script>

    <?php ob_end_flush();
}

?>