<?php

require_once __DIR__ . "/../../logs/logger.php";
require_once __DIR__ . "/user_operations.php";

// Azure: log stream (logging), wwwroot (file view) 

// Adds debug info div:
function include_debug_div() {
    $user_id = authenticate_user();
    if (!$user_id)
        return;
    $user_data = user_data_from_id($user_id);
    if (!$user_data || ($user_data["role"] != 'ADMIN'))
        return;

    ob_start(); ?>

    <div id="debug-div" class="d-block m-3 js-hidden" style="position:fixed;bottom:0px;">
        <div id="debug-content" class="js-collapse debug-collapse tab-content" data-display="block" style="display:none;overflow-y:scroll;overflow-x:auto;">
            <div id="general" class="tab-pane fade show active" role="tabpanel">
                <p><?php echo "SCRIPT_NAME: " . basename($_SERVER["SCRIPT_NAME"]); ?><br>
                <?php echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]; ?></p>
                <p><?php echo "_GET: " . var_export($_GET, true); ?></p>
                <p><?php echo "_POST: " . var_export($_POST, true); ?></p>
                <p><?php echo "_SESSION: " . var_export($_SESSION, true); ?></p>
                <p><?php echo "_COOKIE: " . var_export($_COOKIE, true); ?></p>
            </div>
            <div id="php-errors" class="tab-pane fade" role="tabpanel"></div>
            <div id="logger" class="tab-pane fade" role="tabpanel">
                <?php
                $log_file = __DIR__ . "/../../logs/log.txt";
                $entries = read_entries($log_file);
                create_log_table($entries);
                ?>
            </div>
        </div>
        <nav id="debug-div-nav" class="navbar navbar-dark bg-dark p-1">
            <button class="navbar-toggler" type="button" onclick="toggleDebugDiv()" style="outline:none;box-shadow:none;">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="nav nav-pills navbar-dark bg-dark js-collapse debug-collapse me-auto" role="tablist" data-display="flex" style="display:none">
                <li class="nav-item">
                    <button type="button" class="nav-link active" id="button-general" data-bs-toggle="pill" data-bs-target="#general" role="tab">General</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" id="button-php-errors" data-bs-toggle="pill" data-bs-target="#php-errors" role="tab">PHP Errors</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" id="button-logger" data-bs-toggle="pill" data-bs-target="#logger" role="tab">Logger</button>
                </li>
            </ul>
        </nav>
    </div>

    <script>
        function fetch_and_display(url, elementId) {
            fetch(url, { cache: 'no-store', })
                .then(response => {
                    if (!response.ok)
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.text();
                })
                .then(content => {
                    const lines = content.trim().split('\n[');
                    const reversedContent = '[' + lines.reverse().join('<hr style=\"margin:5px;\">[');
                    const element = document.getElementById(elementId);
                    element.innerHTML = reversedContent;
                    const debugContent = document.getElementById("debug-content");
                    // debugContent.scrollTop = debugContent.scrollHeight;
                })
                .catch(error => {
                    console.error("Error fetching the file:", error);
                });
        }
        document.getElementById("button-php-errors").addEventListener("click", () => {
            // Specify the URL of the file you want to fetch
            const logFile = "../../logs/php_errors.log";
            fetch_and_display(logFile, "php-errors");
        });
        let debugDiv = document.getElementById("debug-div");
        document.addEventListener("click", (event) => {
            // Close debug-div if it is open and clicked outside it:
            if (isDebugDivVisible() && !debugDiv.contains(event.target))
                toggleDebugDiv();
        });
        function isDebugDivVisible() {
            return !debugDiv.classList.contains("js-hidden");
        }
        function toggleDebugDiv() {
            let collapseElementList = debugDiv.querySelectorAll('.js-collapse');
            collapseElementList.forEach((collapseEl) => {
                collapseEl.style.display = (isDebugDivVisible() ? "none" : collapseEl.getAttribute("data-display"));
            });
            debugDiv.classList.toggle("js-hidden");
        }
    </script>

    <?php ob_end_flush();
}

?>