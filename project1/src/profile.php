<?php 

require_once __DIR__ . "/user_operations.php";
require_once __DIR__ . "/shared_elements.php";

$user_id = init_secure_page();
$user_data = user_data_from_id($user_id);

shared_script_start("Profile");
?>

<div class="container">
    <div class="row d-flex justify-content-center mt-5">
        <div class="col" style="max-width:700px;">
            <div class="jumbotron bg-light text-dark p-3" style="border-radius: 5px;">
                <?php
                $s_user_id = htmlspecialchars($user_data["id"]);
                $s_name = htmlspecialchars($user_data["name"]);
                $s_email = htmlspecialchars($user_data["email"]);
                $s_pw_hash = htmlspecialchars($user_data["pw_hash"]);
                $s_status = htmlspecialchars($user_data["status"]);
                $s_role = htmlspecialchars($user_data["role"]);
                echo "<h1 class=\"display-4\">Hello, $s_name!</h1>";
                ?>
                <p class="lead">Here is what we know about you:</p>

                <?php
                echo "ID: $s_user_id <br>";
                echo "Name: $s_name <br>";
                echo "Email: $s_email<br>";
                echo "Password hash: $s_pw_hash<br>";
                echo "Status: $s_status<br>";
                echo "Role: $s_role<br>";
                ?>

                <hr class="my-4">
                <div class="row mb-5">
                    <div class="col-auto">
                        <a href="front.php?logout" class="btn btn-primary">Logout</a>
                    </div>
                    <div class="col-auto">
                        <a href="change_password.php" class="btn btn-primary">Change password</a>
                    </div>
                </div>
                <div>
                    <a href="front.php">Back to frontpage</a>
                </div>
            </div>
        </div>
    </div>
</div>


<?php shared_script_end(); ?>
