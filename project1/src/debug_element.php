<?php

// Azure: log stream (logging), wwwroot (file view) 

// Adds debug info div:
function include_debug_div() {
    $GLOBALS["debug"] = ["how to use this?", "we already have g_logger"];
    ob_start(); ?>

    <div id="debug-div" class="d-block m-3" style="position:fixed;bottom:0px;">
        <nav id="debug-div-nav" class="navbar navbar-dark bg-dark p-1">
            <ul class="nav nav-tabs navbar-dark bg-dark collapse debug-collapse" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" id="button-general" data-bs-toggle="tab" data-bs-target="#general" role="tab">General</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" id="button-php-errors" data-bs-toggle="tab" data-bs-target="#php-errors" role="tab">PHP Errors</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" id="button-logger" data-bs-toggle="tab" data-bs-target="#logger" role="tab">Logger</button>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".debug-collapse" style="outline:none;box-shadow:none;">
                <span class="navbar-toggler-icon"></span>
            </button>
        </nav>
        <div id="debug-content" class="collapse debug-collapse tab-content" style="overflow-y:scroll;overflow-x:auto;">
            <div id="general" class="tab-pane fade show active" role="tabpanel">
                <p><?php echo "SCRIPT_NAME: " . basename($_SERVER["SCRIPT_NAME"]); ?><br>
                <?php echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]; ?></p>
                <p><?php echo "_GET: " . var_export($_GET, true); ?></p>
                <p><?php echo "_POST: " . var_export($_POST, true); ?></p>
                <p><?php echo "_SESSION: " . var_export($_SESSION, true); ?></p>
                <p><?php echo "_COOKIE: " . var_export($_COOKIE, true); ?></p>
                <p><?php echo "_ENV: " . var_export($_ENV, true); ?></p>
            </div>
            <div id="php-errors" class="tab-pane fade" role="tabpanel"></div>
            <div id="logger" class="tab-pane fade" role="tabpanel"></div>
        </div>
    </div>

    <script>
        function fetch_and_display(url, elementId) {
            fetch(url)
                .then(response => response.text())
                .then(content => {
                    const formattedContent = content.replace(/\n/g, "<br>");
                    const element = document.getElementById(elementId);
                    element.innerHTML = formattedContent;
                    const debugContent = document.getElementById("debug-content");
                    debugContent.scrollTop = debugContent.scrollHeight;
                })
                .catch(error => {
                    console.error("Error fetching the file:", error);
                });
        }
        function scrollToBottom(elementId) {
            const element = document.getElementById(elementId);
            setTimeout(() => {
                element.scrollTop = element.scrollHeight;
            }, 0);
        }
        function hideChildren(elementId) {
            const element = document.getElementById(elementId);
            for (let i = 0; i < element.children.length; i++)
                element.children[i].classList.add("d-none");
        }
        document.getElementById("button-php-errors").addEventListener("click", () => {
            // Specify the URL of the file you want to fetch
            const logFile = "../../logs/php_errors.log";
            fetch_and_display(logFile, "php-errors");
        });
        document.getElementById("button-logger").addEventListener("click", () => {
            // Specify the URL of the file you want to fetch
            const logFile = "../../logs/log.txt";
            fetch_and_display(logFile, "logger");
        });
    </script>

    <?php ob_end_flush();
}

?>