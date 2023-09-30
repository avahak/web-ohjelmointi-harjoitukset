<?php

// TODO could allow checkboxes, radio button go in a rows of M, instead of block/inline

// example forms: https://getbootstrap.com/docs/5.0/forms/validation/

// If no special visual appearance is needed, these templates can be used for 
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

// Template for a input with type text/email/password/number
function template_input($type, $name, $label, $placeholder, $label_class, $input_class) {
    $validity = validation_class($name);
    $value = ($type == "password" ? "" : recall($name, true));
    $feedback_div = template_custom_feedback_div($name);
    echo <<<HTML
        <div class="row">
            <div class="$label_class d-flex">
                <label for="$name" class="form-label">$label</label>
            </div>
            <div class="$input_class">
                <input type="$type" class="form-control $validity" id="$name" name="$name" placeholder="$placeholder" value="$value">
                $feedback_div
            </div>
        </div>
        HTML;
}

// NOTE! invalid-feedback div goes in the same div as the last input or it won't show up.
// BUG form-check-inline makes invalid-feedback go in wrong place. https://github.com/twbs/bootstrap/issues/25540
function template_radio($name, $label, $options, $form_check_inline) {
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
                <input class="form-check-input $validity" type="radio" name="$name" id="{$name}{$k}" value="$option" $checked>
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
    $validity = validation_class($name);
    $feedback_div = template_custom_feedback_div($name);
    $value = recall($name, false);
    $label_html = $label ? "<label for=\"$name\" class=\"form-label\">$label</label>" : "";
    echo <<<HTML
        <div>
            $label_html
            <select class="form-select $validity" id="$name" name="{$name}">
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

function template_checkbox($name, $label, $options, $form_check_inline) {
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
                <input class="form-check-input $validity" $checked type="checkbox" id="{$name}{$k}" name="{$name}[]" value="$option">
                <label class="form-check-label" for="{$name}{$k}">$option</label>
            </div>
            HTML;
    }
    echo $feedback_div;
    echo "</div>";
}

// TODO for a file: can check size, extension, check if it is an actual image
function template_file($name, $label, $extensions) {
    echo <<<HTML
        <div class="mb-3">
            <label for="fileInput" class="form-label">$label</label>
            <input type="file" class="form-control" name="file" id="file">
        </div>
        HTML;
}

?>