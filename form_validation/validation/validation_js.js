// JavaScript portion of the JSON rules validation.

"use strict";

const ALLOW_VALID_SUBMIT = true; 
const ALLOW_INVALID_SUBMIT = false;  // use true to debug php validation

// Returns all inputs with given name that are checked in the form.
// Use to get selected values for radio button or checkbox.
function getAllChecked(form, name) {
    let checkedList = [];
    const inputs = form.querySelectorAll('[name="' + name + '"]');
    for (const input of inputs) 
        if (input.checked)
            checkedList.push(input.value);
    return checkedList;
}

// Cuts brackets [] off from the end of the string
function cutBrackets(str) {
    return (str.endsWith("[]") ? str.substring(0, str.length-2) : str);
}

// Returns true iff input was validated already.
function inputWasValidated(input) {
    return (input.classList.contains("is-valid") || input.classList.contains("is-invalid"));
}

// Makes custom-feedback text color match the validity of the input field and assigns text.
function updateFeedbackDiv(form, input, msg) {
    let div = getFeedbackDiv(input);
    if (!div) 
        return;
    div.classList.remove("text-danger");
    div.classList.remove("text-success");
    if (input.classList.contains("is-invalid"))
        div.classList.add("text-danger");
    if (input.classList.contains("is-valid"))
        div.classList.add("text-success");
    div.innerHTML = msg;
}

// Updates the validation status of the input according to msg. 
function updateValidationMessage(form, input, msg) {
    // console.log("updateValidationMessage", input.id, input.name, input.type, msg);
    updateValidity(form, input.name, msg);
    updateFeedbackDiv(form, input, msg);
}

// Returns the custom-invalid-feedback div associated with the input.
function getFeedbackDiv(input) {
    let feedbackId = cutBrackets(input.name) + "-feedback";
    return document.getElementById(feedbackId);
}

// Update all inputs with same name (multiple in case of radio/checkbox):
function updateValidity(form, name, msg) {
    // console.log("updateValidity", name, msg);
    const inputsWithSameName = form.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]');
    // console.log("inputsWithSameName", inputsWithSameName);
    for (const inputNamesake of inputsWithSameName) {
        // console.log("updateValidity found element", inputNamesake);
        if (msg) {
            inputNamesake.classList.add("is-invalid");
            inputNamesake.classList.remove("is-valid");
        }
        else {
            inputNamesake.classList.add("is-valid");
            inputNamesake.classList.remove("is-invalid");
        }
        inputNamesake.setCustomValidity(msg);
    }
}

// Returns appropriate message for rule (with ruleName, ruleValue) of a field (with name):
function invalidFeedbackMessage(name, ruleName, ruleValue) {
    const defaultMsg = ruleName.startsWith("pattern") 
        ? VALIDATION_JSON.VALIDATION_DEFAULT_MESSAGES.pattern
        : VALIDATION_JSON.VALIDATION_DEFAULT_MESSAGES[ruleName];

    let errorMsg = VALIDATION_JSON.VALIDATION_MESSAGES[name]?.[ruleName] || defaultMsg;
    const nameText = VALIDATION_JSON.VALIDATION_MESSAGES[name]?.text || name;

    errorMsg = errorMsg.replace("%1", nameText).replace("%2", ruleValue);
    return errorMsg.charAt(0).toUpperCase() + errorMsg.slice(1);
}

// Validates one input field based on the validation rules. 
// Returns empty string on success and validation error message otherwise.
function validateInput(form, input) {
    let value = input.disabled ? "" : input.value;

    let allChecked = null;
    if (input.type == "radio") {
        allChecked = getAllChecked(form, input.name);
        // console.log("getAllChecked", input.id, allChecked);
        value = (allChecked.length != 0 ? allChecked[0] : "");
    } else if (input.type == "checkbox") {
        allChecked = getAllChecked(form, input.name);
        // console.log("getAllChecked", input.id, allChecked);
        value = (allChecked.length != 0 ? allChecked.join(",") : "");
    } else if (input.type == "select-one") {
        const selectedOption = input.options[input.selectedIndex];
        value = selectedOption.disabled ? "" : value;
    }

    // console.log("validateInput: input, input.type, value = ", input, input.type, value);

    let name = cutBrackets(input.name);
    let rules = VALIDATION_JSON.VALIDATION_RULES[name];
    if (!rules)
        return "";

    let combinedImageCheck = {};    // Combines all checks that require loading an image
    for (let ruleName in rules) {
        let ruleValue = rules[ruleName];

        let isValid = true;

        if ((ruleName == "required") && (ruleValue)) 
            if (value.length == 0) 
                isValid = false;

        if (ruleName == "min_length")
            if (value.length < ruleValue)
                isValid = false;

        if (ruleName == "max_length")
            if (value.length > ruleValue)
                isValid = false;

        if (ruleName.startsWith("pattern")) {
            const ptrn = new RegExp(ruleValue);
            if (!ptrn.test(value))
                isValid = false;
        }

        if ((ruleName == "numeric") && ruleValue) 
            if (isNaN(value)) 
                isValid = false;

        if (ruleName == "min")
            if (value < ruleValue)
                isValid = false;

        if (ruleName == "max")
            if (value > ruleValue)
                isValid = false;

        if (ruleName == "force_equality") {
            let otherInput = document.getElementById(ruleValue);
            if (otherInput.value != value)
                isValid = false;
        }

        if ((ruleName == "min_selected") && (allChecked != null)) 
            if (allChecked.length < ruleValue)
                isValid = false;

        if ((ruleName == "max_selected") && (allChecked != null)) 
            if (allChecked.length > ruleValue)
                isValid = false;

        if ((ruleName == "max_size_mb") && (input.files.length > 0)) {
            const selectedFile = input.files[0];
            const fileSize = selectedFile.size;
            if (fileSize > ruleValue*1024*1024)
                isValid = false;
        }

        if ((ruleName == "accept_extensions") && (input.files.length > 0)) {
            const fileName = input.files[0].name;
            const fileExtension = `.${fileName.split(".").pop()}`.toLowerCase();
            const fileType = input.files[0].type.toLowerCase();
            let isMatch = ruleValue.some((extension) => {
                extension = extension.toLowerCase();
                // console.log("ext", extension, fileExtension, fileType);
                if (extension.startsWith('.')) {
                    // Extension looks like ".txt":
                    return (extension === fileExtension);
                }
                if (extension.endsWith('/*')) {
                    // Extension looks like "image/*":
                    return fileType.startsWith(extension.substring(0, extension.length-2));
                }
                // Extension looks like "application/pdf":
                return (extension === fileType);
            });
            if (!isMatch)
                isValid = false;
        }

        if ((ruleName == "verify_is_image") && (ruleValue) && (input.files.length > 0)) 
            combinedImageCheck["verify_is_image"] = ruleValue;

        if ((ruleName == "image_preview") && (ruleValue) && (input.files.length > 0))
            combinedImageCheck["image_preview"] = ruleValue;

        // console.log("validation result for ", ruleName, isValid);

        if (!isValid) {
            const errorMsg = invalidFeedbackMessage(name, ruleName, ruleValue);
            updateValidationMessage(form, input, errorMsg);
            return;
        }
    }

    updateValidationMessage(form, input, "");

    // Do image checks in one go:
    // NOTE: image loading is done asynchronously so validation is not immediate.
    if (Object.keys(combinedImageCheck).length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.src = event.target.result;
            console.log(combinedImageCheck);
            img.onload = function() {
                // File is an image:
                if ("image_preview" in combinedImageCheck) {
                    const preview = document.getElementById(name + "_preview");
                    const preview_container = document.getElementById(name + "_preview_container");
                    if (preview_container && preview) {
                        preview_container.classList.remove("d-none");
                        preview.src = event.target.result;
                    }
                }
                // console.log("img load success!")
                updateValidationMessage(form, input, "");
            };
            img.onerror = function() {
                // File is not an image:
                if ("image_preview" in combinedImageCheck) {
                    const preview_container = document.getElementById(name + "_preview_container");
                    if (preview_container)
                        preview_container.classList.add("d-none");
                }
                // console.log("img load failed!")
                const errorMsg = invalidFeedbackMessage(name, "verify_is_image", true);
                updateValidationMessage(form, input, errorMsg);
            };
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Calls validateInput for every input in the form except for the ones already validated.
// Returns true if all inputs are valid.
function validateForm(form) {
    try {
        let inputFields = form.querySelectorAll("input, select, textarea");
        let isValid = true;
        for (const input of inputFields) {
            // If a field is already validated, use that:
            if (input.classList.contains("is-invalid")) {
                isValid = false;
                continue;
            }
            if (input.classList.contains("is-valid")) 
                continue;

            if (input.getAttribute("data-uploaded-file") && !input.classList.contains("user-modified")) {
                // A file has been stored and another has not been selected - do not validate:
                continue;
            }
            validateInput(form, input);
            if (input.classList.contains("is-invalid")) 
                isValid = false;
        };
        return isValid;
    } catch (error) {
        console.log("Exception caught during validation!", error);
    }
}

// Do the following when page is loaded, could be fresh form or after submit.
document.addEventListener("DOMContentLoaded", function() {
    let form = document.getElementById(VALIDATION_JSON.FORM_ID);

    // Validate form on submit:
    form.addEventListener("submit", function(event) {
        let allow_submit = true;
        try {
            if (validateForm(form)) {
                if (!ALLOW_VALID_SUBMIT) 
                    allow_submit = false;
            } else {
                if (!ALLOW_INVALID_SUBMIT) 
                    allow_submit = false;
            }
            if (!allow_submit) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Form will get submitted - disable submit button to prevent duplicate:
                const submitButton = document.getElementById("submit_button");
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = "Submitting...";
                }
            }
        } catch (err) {
            console.log("Exception during form submit", err);
        }
    });


    // Inputs with errors do not show automatically as red after server-side validation 
    // since even though their feedback divs contain errors, setCustomValidity has 
    // not been called. Call it here if needed:
    let inputFields = form.querySelectorAll("input, select, textarea");
    inputFields.forEach(function(input) {
        if (!inputWasValidated(input)) 
            return;
        let name = cutBrackets(input.name);
        let div = getFeedbackDiv(input);
        if (!div)
            return;
        if (input.classList.contains("user-modified")) {
            console.log("DEBUG: ", input, div.innerHTML.length);
            if ((VALIDATION_JSON.VALIDATION_RULES[name]?.prevent_recall) || (VALIDATION_JSON.VALIDATION_RULES[name]?.store_file === false))
                if (div.innerHTML.trim().length == 0) {
                    // Input was modified and prevented from recall and feedback is empty.
                    // Therefore we should tell the user to refill it.
                    const msg = VALIDATION_JSON.VALIDATION_MESSAGES[name]?.prevent_recall 
                        ?? VALIDATION_JSON.VALIDATION_DEFAULT_MESSAGES.prevent_recall;
                    updateFeedbackDiv(form, input, msg);
                }
        }
        if (input.classList.contains("is-invalid")) {
            // Update validity for invalid inputs directly after server-side validation:
            // (Without this we get green boxes and red errors under.)
            updateValidity(form, input.name, div.innerHTML);
        }
    });

    // Listen to inputs:
    inputFields.forEach(function(input) {
        input.addEventListener("input", function() {
            // console.log("inputFields element is", input.id);

            input.classList.add("user-modified");

            // Close alerts that can be closed:
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let close = alert.querySelector('.btn-close');
                if (close)
                    close.click();
            });

            // If input was already validated, keep doing it:
            // Also, always validate file inputs:
            if (inputWasValidated(input) || (input.type == "file")) {
                console.log("input event on", input)
                validateInput(form, input);
                // Go through VALIDATION_TRIGGERS for the input 
                // to see if we need to validate other inputs as well:
                const triggerList = VALIDATION_JSON.VALIDATION_TRIGGERS[cutBrackets(input.name)];
                if (triggerList) {
                    for (const otherInputName of triggerList) {
                        const otherInputs = form.querySelectorAll('[name="' + otherInputName + '"], [name="' + otherInputName + '[]"]');;
                        for (const otherInput of otherInputs)
                            validateInput(form, otherInput);
                    }
                }
            }
        });
    });

    // Make reset buttons for file inputs work:
    inputFields.forEach(function(input) {
        const name = cutBrackets(input.name);
        const reset_button = document.getElementById(name + "_reset");
        if (!reset_button || (reset_button.tagName.toLowerCase() != "button"))
            return;
        reset_button.addEventListener("click", () => {
            // Reset the value of the file input
            input.value = "";
            input.classList.remove("user_modified");
            if (input.hasAttribute("data-uploaded-file")) {
                // Restore file upload to temporary stored file:
                console.log("RESET WITH data-uploaded-file");
                updateValidity(form, name, "");
                const original = input.getAttribute("data-uploaded-file");
                let msg = invalidFeedbackMessage(name, "store_file", true);
                msg = msg.replace("%f", original);
                updateFeedbackDiv(form, input, msg);
            } else {
                // Reset to unvalidated initial state:
                input.classList.remove("is-invalid");
                input.classList.remove("is-valid");
                updateFeedbackDiv(form, input, "");
            }
            // Remove preview if it exists:
            const preview_container = document.getElementById(input.name + "_preview_container");
            if (preview_container)
                preview_container.classList.add("d-none");
        });
    });
});
