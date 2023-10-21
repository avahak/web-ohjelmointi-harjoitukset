<?php 

define("ENTRIES_PER_PAGE", 10);

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/init.php";
require_once __DIR__ . "/user_operations.php";

// Check that the user is admin:
$user_id = init_secure_page();
$user_data = user_data_from_id($user_id);
if ($user_data["role"] != "ADMIN") {
    header("location: front.php");
    exit();
}

// Check if javascript is communicating with AJAX:
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $php_input = json_decode(file_get_contents("php://input"), true);
    if (($php_input["request"] ?? "") == "get_user_data") {
        // Write out user data:
        $data = user_data_from_id($php_input["user_id"]);
        if ($data)
            echo json_encode($data);
    } else if (($php_input["request"] ?? "") == "update_user") {
        // Try to update user and write info on success/failure:
        $parts = [];
        $arguments = [""];
        foreach ($php_input["new_values"] as $key => $value) {
            $parts[] = "$key=?";
            $arguments[] = $value;
        }
        $query = "UPDATE users SET " . implode(", ", $parts) . " WHERE id=?";
        if (rand(0, 2) == 0)        // Simulates error to test modal-error:
            $query = "_" . $query;
        $arguments[0] = $query;
        $arguments[] = $php_input["user_id"];
        $result = call_user_func_array(array($GLOBALS["g_conn"], 'substitute_and_execute'), $arguments);
        if ($result["success"])
            $result["value"] = "";
        echo json_encode($result);
    }
    exit();
}

// Returns number of users.
function count_users() {
    $query = "SELECT COUNT(*) FROM users";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query);
    return $result["success"] ? $result["value"]->fetch_row()[0] : 0;
}

// Returns the field names of the users table
function select_fields() {
    // $query = "SELECT column_name FROM information_schema.columns WHERE table_name=?";
    $query = "SHOW COLUMNS FROM users";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query);
    if (!$result["success"])
        return [];
    $columns = $result["value"]->fetch_all(MYSQLI_ASSOC);
    return array_column($columns, "Field");
}

// Returns all entries from the specified part of the users table
function select_users($offset, $limit) {
    $order_by = $_GET["order_by"] ?? "id";
    $query = "SELECT * FROM users ORDER BY $order_by LIMIT ? OFFSET ?";
    $result = $GLOBALS["g_conn"]->substitute_and_execute($query, $limit, $offset);
    if (!$result["success"])
        return [];
    return $result["value"]->fetch_all(MYSQLI_ASSOC);
}

// Clickable page numbers:
function create_page_item($x, $current_page, $max_page) {
    $dots = ($x == "dots") ? "dots" : "";
    if (!$dots && ($x < 1 || $x > $max_page))
        return;
    $current = ($x == $current_page) ? "current" : "";
    echo "<li class=\"page-item $dots $current\">";
    if ($dots)
        echo "<span class=\"page-link\">&hellip;</span>";
    else
        echo "<span class=\"page-link\" onclick=\"pageNumberClick(event)\" data-js-page=\"$x\" aria-label=\"Page $x\">$x</span>";
    echo "</li>";
}

$status_options = $GLOBALS["g_conn"]->extract_range("users", "status");
$role_options = $GLOBALS["g_conn"]->extract_range("users", "role");

$user_count = count_users();
$fields = select_fields();
if ((count($fields) == 0) || ($user_count == 0))
    exit("ERROR: No data!");

$max_page = (int)ceil($user_count / ENTRIES_PER_PAGE);
$page = min(max((int)($_GET["page"] ?? 0), 1), $max_page);
$table = select_users(($page-1)*ENTRIES_PER_PAGE, ENTRIES_PER_PAGE);

shared_script_start("Admin User Management");

?>
<style>
    #user-table {
        table-layout: fixed;
        max-width: 100%;
        border-collapse: separate;
        border-spacing: 0px 2px;
    }
    #user-table td, #user-table th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 4px;
    }
    #user-table tr {
        position: sticky;
    }

    .dark-mode .page-link {
        background-color: #335;
        border-color: #555;
        color: #fff;
    }
    .dark-mode .page-link:hover {
        background-color: #557;
        cursor: pointer;
    }
    .dark-mode .dots.page-item .page-link:hover {
        background-color: #335;
        pointer-events: none;
    }
    .dark-mode .page-item.current .page-link {
        background-color: #557;
        pointer-events: none;
    }
    .exampleModal {
        background: #555;
        color: #f0f;
    }

    /* NOTE: maybe gather all dark-mode CSS in one .css file */
    /* Define dark mode for modal */
    .modal-dark {
        background-color: #333; /* Dark background color */
    }

    .modal-dark-content {
        background-color: #444; /* Dark content background color */
        border: 1px solid #222; /* Dark border color */
    }

    .modal-dark .modal-title,
    .modal-dark .modal-body,
    .modal-dark .btn-close,
    .modal-dark .modal-footer {
        color: #fff; /* Light text color for contrast */
    }

    .modal-dark-footer {
        background-color: #444; /* Dark footer background color */
        border-top: 1px solid #222; /* Dark border color for the footer */
    }

    .modal-data-span {
        word-break: break-all;
    }

    #modal-error {
        background: #aaa;
        color: #a00;
        border: 2px solid #a00;
        border-radius: 4px;
        padding: 5px;
        margin: 5px;
    }
</style>

<div class="container mt-3">
    <div id="user-table-nav" aria-label="Table Pagination" class="dark-mode m-0 p-0">
        <nav class="m-0 p-0">
            <ul class="pagination m-0 p-0">
                <?php if ($max_page > 9) { ?>
                <li class="page-item me-4">
                    <div class="form-inline page-link m-0 p-0" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control" id="table-input-page" placeholder="Page" autocomplete="off" style="max-width:75px">
                            <div class="input-group-append">
                                <button id="table-button-go" onclick="pageNumberClick(event)" class="btn btn-primary">Go</button>
                            </div>
                        </div>
                    </div>
                </li>
                <?php } ?>
                <?php
                if ($max_page <= 9) {
                    for ($k = 1; $k <= $max_page; $k++)
                        create_page_item($k, $page, $max_page);
                } else {
                    if ($page >= 4)
                        create_page_item(1, $page, $max_page);
                    if ($page >= 5) 
                        create_page_item(($page == 5 ? 2 : "dots"), $page, $max_page);
                    if ($page >= 3)
                        create_page_item($page-2, $page, $max_page);
                    if ($page >= 2)
                        create_page_item($page-1, $page, $max_page);
                    create_page_item($page, $page, $max_page);
                    if ($page <= $max_page-1)
                        create_page_item($page+1, $page, $max_page);
                    if ($page <= $max_page-2)
                        create_page_item($page+2, $page, $max_page);
                    if ($page <= $max_page-4)
                        create_page_item(($page == $max_page-4 ? $max_page-1 : "dots"), $page, $max_page);
                    if ($page <= $max_page-3)
                        create_page_item($max_page, $page, $max_page);
                }
                ?>
            </ul>
        </nav>
    </div>

    <table id="user-table" class="table table-dark table-bordered">
        <thead>
            <tr>
                <?php foreach ($fields as $field) { ?>
                <th id="table-header-<?= $field ?>" data-js-field="<?= $field ?>" class="bg-secondary" onclick="tableHeaderClick(event)"><?= $field ?></th>
                <?php } ?>
                <th style="background-color: transparent;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table as $entry) { ?>
                <tr>
                    <?php foreach ($fields as $field) { ?>
                    <td><?php echo $entry[$field]; ?></td>
                    <?php } ?>
                    <td style="background:transparent;"><button id="button-edit-<?php echo $entry["id"]; ?>" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#user-modal" data-js-id="<?php echo $entry["id"]; ?>">Edit</button></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div class="modal fade dark-mode" id="user-modal" tabindex="-1" aria-labelledby="user-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dark">
            <div class="modal-content modal-dark-content">
                <div class="modal-header">
                    <h5 class="modal-title text-light" id="user-modal-label">User Information</h5>
                    <button type="button" class="btn-close btn-close-white text-light" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-light flex">
                    <img id="modal-data-profile_picture" class="rounded float-end d-none" src="">
                    <ul>
                        <li>
                            ID: <span class="modal-data-span" id="modal-data-id"></span>
                        </li>
                        <li>
                            Name: <span class="modal-data-span" id="modal-data-name"></span>
                        </li>
                        <li>
                            Email: <span class="modal-data-span" id="modal-data-email"></span>
                        </li>
                        <li>
                            Profile picture: <span class="modal-data-span" id="modal-data-profile_picture_path"></span>
                        </li>
                        <li>
                            Pw hash: <span class="modal-data-span" id="modal-data-pw_hash"></span>
                        </li>
                        <li class="my-1">
                            <!-- <span class="modal-data-span" id="modal-data-status"></span> -->
                            <div class="row">
                                <div class="col-12 col-sm-4">
                                    <label class="form-label">Status:</label>
                                </div>
                                <div class="col-12 col-sm-8">
                                    <select id="modal-select-status" class="form-select selectpicker" name="status">
                                        <?php 
                                        foreach ($status_options as $key => $value) {
                                            $s_key = htmlspecialchars($key);
                                            $s_value = htmlspecialchars($value);
                                            echo "<option value='" . $s_value . "'>" . $s_value . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </li>
                        <li class="my-1">
                            <!-- <span class="modal-data-span" id="modal-data-role"></span> -->
                            <div class="row">
                                <div class="col-12 col-sm-4">
                                    <label class="form-label">Role:</label>
                                </div>
                                <div class="col-12 col-sm-8">
                                    <select id="modal-select-role" class="form-select selectpicker" name="role">
                                        <?php 
                                        foreach ($role_options as $key => $value) {
                                            $s_key = htmlspecialchars($key);
                                            $s_value = htmlspecialchars($value);
                                            echo "<option value='" . $s_value . "'>" . $s_value . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div id="modal-error" class="d-none"></div>
                </div>
                <div class="modal-footer modal-dark-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="modal-button-save" type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>

let jsGlobals = {};

// Called when save button is pressed in modal:
function updateUser() {
    const headers = new Headers({
        'Content-Type': 'application/json',
    });
    const postData = {
        request: "update_user",
        user_id: jsGlobals.id,
        new_values: {
            status: document.getElementById("modal-select-status").value,
            role: document.getElementById("modal-select-role").value,
        },
    };
    const currentURL = window.location.origin + window.location.pathname;
    const requestOptions = {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(postData),
    };
    fetch(currentURL, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            console.log(result)
            if (result["success"] == true) {
                const newURL = currentURL + window.location.search;
                window.location.href = newURL;
            }
            else 
                throw new Error('Error operating with the database: ' + result["value"]);
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            safeAssign(document.getElementById("modal-error"), error);
            document.getElementById("modal-error")?.classList.remove("d-none");
        });
}

// Javascript version of htmlspecialchars:
function safeAssign(element, text) {
    if (element) {
        const textNode = document.createTextNode(text);
        element.innerHTML = "";
        element.appendChild(textNode);
    }
}

// Called when a page number is clicked:
function pageNumberClick(event) {
    let page = event.target.getAttribute("data-js-page");
    if (event.target.id == "table-button-go")
        page = document.getElementById("table-input-page")?.value;
    const searchParams = new URLSearchParams(window.location.search);
    const newSearchParams = new URLSearchParams("");
    newSearchParams.append("page", page);
    if (searchParams.has("order_by")) 
        newSearchParams.append("order_by", searchParams.get("order_by"));
    const newSearch = newSearchParams.toString();
    const newURL = `${window.location.origin}${window.location.pathname}?${encodeURI(newSearch)}`;
    window.location.href = newURL;
}

// Called when a table header is clicked:
function tableHeaderClick(event) {
    field = event.target.getAttribute("data-js-field");
    const searchParams = new URLSearchParams(window.location.search);
    const newSearchParams = new URLSearchParams("");
    newSearchParams.append("order_by", field);
    if (searchParams.has("page")) 
        newSearchParams.append("page", searchParams.get("page"));
    const newSearch = newSearchParams.toString();
    const newURL = `${window.location.origin}${window.location.pathname}?${encodeURI(newSearch)}`;
    window.location.href = newURL;
}

document.addEventListener("DOMContentLoaded", () => {
    // Save button:
    const button = document.getElementById("modal-button-save");
    button.addEventListener("click", () => {
        updateUser();
    });

    // Edit buttons:
    const buttons = document.querySelectorAll("button");
    buttons.forEach((button) => {
        if (button.id.startsWith("button-edit")) {
            const user_id = button.getAttribute("data-js-id");
            const headers = new Headers({
                'Content-Type': 'application/json',
            });
            const postData = {
                request: "get_user_data",
                user_id: user_id,
            };
            button.addEventListener("click", () => {
                // Hide error div:
                document.getElementById("modal-error")?.classList.add("d-none");

                const currentURL = window.location.origin + window.location.pathname;
                const requestOptions = {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(postData),
                };
                fetch(currentURL, requestOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // console.log('fetch data: ', data);
                        for (const key in data) {
                            jsGlobals[key] = data[key];
                            const element = document.getElementById("modal-data-" + key);
                            if (element)
                                safeAssign(element, data[key]);
                        }
                        // Show the profile picture if exists:
                        const profile_picture = document.getElementById("modal-data-profile_picture");
                        profile_picture.classList.add("d-none");
                        if (data["profile_picture_path"]) {
                            profile_picture.setAttribute("src", "<?php echo $GLOBALS["CONFIG"]["SITE"]; ?>" + data["profile_picture_path"]);
                            profile_picture.classList.remove("d-none");
                        }
                        // Set status and role:
                        ["status", "role"].forEach((field) => {
                            const selectElement = document.getElementById("modal-select-" + field);
                            for (var i = 0; i < selectElement.options.length; i++) {
                                if (selectElement.options[i].text === data[field]) {
                                    selectElement.selectedIndex = i;
                                    break;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('There was a problem with the fetch operation:', error);
                    });
            });
        }
    });
});

</script>

<?php shared_script_end(); ?>