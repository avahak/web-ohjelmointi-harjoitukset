<?php

// If special visual appearance is not needed, these templates can be used for 
// fast form setup.

// Add the feedback div:
// NOTE! We are using a custom feedback div to show invalidation errors, 
// not Bootstraps invalid-feedback. Reason: Bootstrap invalid-feedback is 
// very inflexible as to where the div has to be placed:
// invalid-feedback div has to go in the same div as the last input or it won't show up.
// Also, BUG form-check-inline makes invalid-feedback go in wrong place: https://github.com/twbs/bootstrap/issues/25540
function template_custom_feedback_div($name) {
    return "<div class=\"custom-invalid-feedback text-danger\" id=\"$name-feedback\">" . 
        custom_feedback($name) . "</div>";
}

// Template for a input with type text/email/password/number:
function template_input($type, $name, $label, $placeholder, $label_class, $input_class) {
    $user_modified = user_modified($name);
    $validity = validation_class($name);
    $value = recall($name, true);
    $feedback_div = template_custom_feedback_div($name);
    echo <<<HTML
        <div class="row">
            <div class="$label_class d-flex">
                <label for="$name" class="form-label">$label</label>
            </div>
            <div class="$input_class">
                <input type="$type" class="form-control $validity $user_modified" id="$name" name="$name" placeholder="$placeholder" value="$value">
                $feedback_div
            </div>
        </div>
        HTML;
}

// Template for a textarea:
function template_textarea($name, $label, $placeholder, $rows) {
    $user_modified = user_modified($name);
    $validity = validation_class($name);
    $value = recall($name, true);
    $feedback_div = template_custom_feedback_div($name);
    echo <<<HTML
        <div class="form-group">
            <label for="$name" class="form-label">$label</label>
            <div>
                <textarea class="form-control $validity $user_modified" rows="$rows" id="$name" name="$name" placeholder="$placeholder">$value</textarea>
            </div>
            $feedback_div
        </div>
        HTML;
}

// Template for radio button. Use $form_check_inline=true to put options on same line.
function template_radio($name, $label, $options, $form_check_inline) {
    $user_modified = user_modified($name);
    $validity = validation_class($name);
    $value = recall($name, false);
    $feedback_div = template_custom_feedback_div($name);
    $inline = ($form_check_inline ? "form-check-inline" : "");
    echo <<<HTML
        <div class="form-group">
            <div>
                <label class="form-label">$label</label>
            </div>
        HTML;
    for ($k = 0; $k < count($options); $k++) {
        $option = $options[$k];
        $checked = ($option == $value ? "checked" : "");
        echo <<<HTML
            <div class="form-check $inline">
                <input class="form-check-input $validity $user_modified" type="radio" name="$name" id="{$name}{$k}" value="$option" $checked>
                <label class="form-check-label" for="{$name}{$k}">
                    $option
                </label>
            </div>
            HTML;
    }
    echo $feedback_div;
    echo "</div>";
}

// Template for select
function template_select($name, $label, $options, $disabled_indexes, $selected_index=0) {
    $user_modified = user_modified($name);
    $validity = validation_class($name);
    $feedback_div = template_custom_feedback_div($name);
    $value = recall($name, false);
    $label_html = $label ? "<label for=\"$name\" class=\"form-label\">$label</label>" : "";
    echo <<<HTML
        <div>
            $label_html
            <select class="form-select $validity $user_modified" id="$name" name="{$name}">
        HTML;
    for ($k = 0; $k < count($options); $k++) {
        $option = $options[$k];
        $disabled = (in_array($k, $disabled_indexes) ? "disabled" : "");
        if (!$value)
            $selected = ($k === $selected_index ? "selected" : "");
        else 
            $selected = ($option == $value ? "selected" : "");
        echo "<option value=\"$option\" $disabled $selected>" . $option . "</option>";
    }
    echo "</select>";
    echo $feedback_div;
    echo "</div>";
}

// Template for checkboxes
function template_checkbox($name, $label, $options, $form_check_inline) {
    $user_modified = user_modified($name);
    $validity = validation_class($name);
    $feedback_div = template_custom_feedback_div($name);
    $values = recall($name, false);
    $inline = ($form_check_inline ? "form-check-inline" : "");
    $label_html = $label ? "<div><label class=\"form-label\">$label</label></div>" : "";
    echo "<div>$label_html";
    for ($k = 0; $k < count($options); $k++) {
        $option = $options[$k];
        $checked = (($values && in_array($option, $values)) ? "checked" : "");
        echo <<<HTML
            <div class="form-check $inline">
                <input class="form-check-input $validity $user_modified" $checked type="checkbox" id="{$name}{$k}" name="{$name}[]" value="$option">
                <label class="form-check-label" for="{$name}{$k}">$option</label>
            </div>
            HTML;
    }
    echo $feedback_div;
    echo "</div>";
}

// Template for a file upload input.
function template_file_upload($name, $label) {
    echo "<label class=\"form-label\" for=\"$name\">$label</label>";

    $uploaded_file = $_SESSION["form_validation_temporary_files"][$name]["original"] ?? ""; 
    $data_uploaded_file = "";
    if ($uploaded_file)
        $data_uploaded_file = "data-uploaded-file=\"" . htmlspecialchars($uploaded_file) . "\"";
    $vc = validation_class($name);
    $um = (($_FILE[$name]["name"] ?? "") ? "user-modified" : "");
    
    ob_start() ?>
    <div class="input-group">
        <input type="file" class="form-control <?= "$um $vc" ?>" name="<?= $name ?>" id="<?= $name ?>" <?= $data_uploaded_file ?>>
        <button type="button" class="btn btn-secondary" id="<?= $name ?>_reset">Reset</button>
    </div>
    <div class="custom-invalid-feedback <?= validation_text_color($name) ?>" id="<?= $name ?>-feedback"><?= custom_feedback($name) ?></div>
    <?php ob_end_flush();
}

// Creates an image preview div for a file upload input.
function template_file_upload_image_preview($name, $label, $max_size) {
    ob_start() ?>
    <div id="<?= $name ?>_preview_container" class="d-none">
        <p class="form-label"><?= $label ?></p>
        <img id="<?= $name ?>_preview" src="" alt="Image Preview" style="max-width:<?= $max_size ?>px;max-height:<?= $max_size ?>px;width:auto;height:auto;">
    </div>
    <?php ob_end_flush();
}

?>