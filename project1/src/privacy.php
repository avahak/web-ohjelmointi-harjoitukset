<?php 

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/init.php";

init();

if (($_SERVER["REQUEST_METHOD"] == "GET") && (isset($_GET["logout"]))) {
    logout();
}

shared_script_start("Info");
?>

<div class="container mb-3" style="max-width:700px">

    <h1 class="my-3">Privacy Statement</h1>

    <p>Your privacy is important to us and we have taken steps to protect your data to the best of our abilities. This privacy statement outlines how we collect, use, and safeguard your data:</p>

    <ol>
        <li>
            <strong>Data Protection:</strong>
            <p>We prioritize the security of your data to the best of our abilities.</p>
        </li>
        <li>
            <strong>Data Usage:</strong>
            <p>Your data will only be used for the specific purposes for which it was provided.</p>
        </li>
        <li>
            <strong>Third-Party Services:</strong>
            <p>We do not share your personal information with third parties without your consent.</p>
        </li>
        <li>
            <strong>Cookies:</strong>
            <p>We may use cookies for site functionality and analytics, but we do not use them to track or collect personally identifiable information.</p>
        </li>
        <li>
            <strong>Opt-Out:</strong>
            <p>You have the right to opt-out of communications or request the removal of your personal data.</p>
        </li>
        <li>
            <strong>Data Retention:</strong>
            <p>We retain your data only for as long as necessary to fulfill the purposes for which it was collected.</p>
        </li>
        <li>
            <strong>Updates:</strong>
            <p>We may update our privacy statement to reflect changes in our practices. Please check this page periodically for updates.</p>
        </li>
    </ol>

    <p>By using our website, you acknowledge and accept the limitations of the security measures we have in place. If you have any concerns about your privacy or data security, please feel free to 
        <a href="contact.php">contact us</a>.</p>

    <p>Last Updated: 17.10.2023.</p>

</div>

<?php shared_script_end(); ?>
